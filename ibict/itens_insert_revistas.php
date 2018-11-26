
<?php

#Connecting to Wordpress
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";

define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/revistas/wp-blog-header.php' );

#Generating object instances for Collection, Metadata, Items, and Item_Metadata
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$metadataRepo = \Tainacan\Repositories\Metadata::get_instance();
$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

#################################

#Setting relational collections
$responsaveis_collection = $collectionsRepo->fetch(['name'=>'Responsáveis'], 'OBJECT');
$responsaveis_collection = $responsaveis_collection[0];

$instituicoes_collection = $collectionsRepo->fetch(['name'=>'Instituições'], 'OBJECT');
$instituicoes_collection = $instituicoes_collection[0];

#Array Itens de Pessoa
echo "Recuperando itens dos responsáveis \n";
$itens_responsaveis = $itemsRepo->fetch(['title'=>'', 'posts_per_page'=> '-1'],$responsaveis_collection, 'OBJECT');

foreach ($itens_responsaveis as $item_responsavel){
	
	$itemMetadataResponsavel = $itemMetadataRepo->fetch($item_responsavel,$metadata[0], ['title'=>'ID Responsável']);
	
	$array_responsaveis [$itemMetadataResponsavel[0]->get_value()] = $item_responsavel;
}

#Array Itens de Entidade
echo "Recuperando itens das instituições \n";
$itens_instituicoes = $itemsRepo->fetch(['title'=>'', 'posts_per_page'=> '-1'],$instituicoes_collection, 'OBJECT');

foreach ($itens_instituicoes as $item_instituicao){
	
	$itemMetadataInstituicao = $itemMetadataRepo->fetch($item_instituicao,$metadata[0], ['title'=>'ID Instituicao']);
	
	$array_instituicoes [$itemMetadataInstituicao[0]->get_value()] = $item_instituicao;
}

#################################

#Getting the Colletion
$collection = $collectionsRepo->fetch(['name'=>'Portal de Revistas'], 'OBJECT');
$collection = $collection[0];


$fh = fopen("portal_revistas.csv", "r") or die("ERROR OPENING DATA");

while (($data = fgetcsv($fh, 0, ",")) == TRUE){
	$linecount++;
}
fclose($fh);


#Getting metadata title from csv array

if (($handle = fopen("portal_revistas.csv", "r")) == TRUE) {
	
	$cont = 0;
	
	while (($data = fgetcsv($handle, 0, ",")) == TRUE){
		
		if($cont == 0){
			
			echo "Tratando primeira linha \n";
			$headers = array_map('trim', $data);
			
		}else{
			
			$item = new \Tainacan\Entities\Item();
			
			$item->set_title($data[0]);
			$item->set_status('publish');
			$item->set_collection($collection);
			
			if ($item->validate()) {
				
				$item = $itemsRepo->insert($item);
				for ($i = 0; $i <=sizeof($data); $i++) {
					
					$metadatum = $metadataRepo->fetch(['name' => $headers[$i]], 'OBJECT');
					$metadatum = $metadatum[0];
					
					
					if ($metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Taxonomy'){
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$taxonomy_value = explode("||",$data[$i]);
						$itemMetadata->set_value(array_unique($taxonomy_value));
						
					} else if ($metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Relationship'){
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$relationship_value = explode("||",$data[$i]);
						$relationship_array = [];
						
						if (strpos($metadatum->get_name(), 'Instituição')!== false){
							foreach($relationship_value as $item_id){
								echo $array_instituicoes[$item_id]->get_id();
								echo "\n";
								$relationship_array [] = $array_instituicoes[$item_id]->get_id();
							}
						}
						else if (strpos($metadatum->get_name(), 'Responsável')!== false){
							foreach($relationship_value as $item_id){
								$relationship_array [] = $array_responsaveis[$item_id]->get_id();
							}
						}
						
						$itemMetadata->set_value($relationship_array);
					
					} else if (strpos($metadatum->get_name(), 'MVL')!== false){
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$metadata_mvl = explode("||",$data[$i]);
						if (sizeof($metadata_mvl == 0)){
								$itemMetadata->set_value('');
						}else{
							$itemMetadata->set_value(array_unique($metadata_mvl));
						}
						$itemMetadata->set_value(array_unique($metadata_mvl));
					
					}else{
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$itemMetadata->set_value($data[$i]);
					}
					
					if ($itemMetadata->validate()) {
						
						$itemMetadataRepo->insert($itemMetadata);
						
					}else {
						echo 'Erro no metadado ', $metadatum->get_name(), ' no item ', $data[0];
						$erro = $itemMetadata->get_errors();
						echo var_dump($erro);
					}
				}
				if ($item->validate()) {
					$item = $itemsRepo->insert($item);
					echo 'Item ', $data[0], ' - inserted', "\n";
					echo $linecount-$cont, ' remain', "\n";
					echo ($cont/$linecount)*100, '% Completed', "\n" ,"\n";
				}else{
					echo 'Erro no preenchientos dos campos', $cont, "\n";
					$errors = $item->get_errors();
					var_dump($errors);
					echo  "\n\n";
					die;
				}
				
			}else {
				echo 'Erro na linha ', $cont;
				echo  "\n\n";
				var_dump($item);
				echo  "\n\n";
				die;
			}
			
		}
		$cont+=1;
	}
fclose($handle);
}


?>
