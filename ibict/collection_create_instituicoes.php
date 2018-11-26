#OBS. ALTERAR A EXIBIÇÃO DOS METADADOS PARA NUNCA MOSTRAR.

<?php
#Connecting with Wordpress:
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );

#Path of wp
require( '/var/www/html/revistas/wp-blog-header.php');

$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$metadataRepo = \Tainacan\Repositories\Metadata::get_instance();
$taxonomyRepo = \Tainacan\Repositories\Taxonomies::get_instance();


#Create Collection and Metadata:
$collection = new \Tainacan\Entities\Collection();
$collection->set_name('Instituições');
$collection->set_status('publish');
$collection->set_description('Coleção com informações das Instituições.');

$cont = 0;
if ($collection->validate()) {
	$insertedCollection = $collectionsRepo->insert($collection);

	if (($handle = fopen("instituicoes_metadados.csv", "r")) == TRUE) {
		
		$collection_core_metadata = $metadataRepo->get_core_metadata($insertedCollection);
		
		while (($data = fgetcsv($handle, 0, ",")) == TRUE){
			if ($cont == 0){
				echo "Pulando linha das colunas", "\n";
			} else{
				if (trim($data[3]) == 'Description' OR trim($data[3]) == 'Title'){
					foreach ($collection_core_metadata as $coreMetadata) {
						if($coreMetadata->get_name() == $data[3]){
							$coreMetadata->set_name($data[0]);
							$coreMetadata->set_description($data[1]);
							echo "Atualizando Core Field: ", $data[0], "\n";
						
							if ($coreMetadata->validate()){
								$insertedMetadata = $metadataRepo->insert($coreMetadata);
							}else {
								$erro = $coreMetadata->get_errors();
								var_dump($erro);
							}
						}
					}
				}else if (trim($data[3]) != 'Description' AND trim($data[3]) != 'Title'){
					echo 'Metadata for Taxonomy or Relationship';
					
					if (trim($data[2]) == 'Tainacan\Metadata_Types\Taxonomy'){
						
						$taxonomy = new \Tainacan\Entities\Taxonomy;
						$taxonomy->set_name(trim($data[0]));
						$taxonomy->set_description("Taxonomia para o metadado ". trim($data[0]));
						$taxonomy->set_allow_insert('yes');
						$taxonomy->set_status('publish');
						
						if ($taxonomy->validate()) {
							$insertedTaxonomy = $taxonomyRepo->insert($taxonomy);
							echo 'Taxonomy created with ID -  ' . $taxonomy->get_id(), "\n";
							
						} else {
								$error = $taxonomy->get_errors();
								var_dump($error);
						}
						
						$metadata_type_options = ['taxonomy_id' => $insertedTaxonomy->get_id(), 'input_type' => 'tainacan-taxonomy-tag-input', 'allow_new_terms' => 'yes', 'multiple'=>'yes'];
						
						$metadado = new \Tainacan\Entities\Metadatum();
						$metadado->set_collection($insertedCollection);
						$metadado->set_name(trim($data[0]));
						$metadado->set_description($data[1]);
						$metadado->set_metadata_type(trim($data[2]));
						$metadado->set_multiple('yes');
						$metadado->set_status('publish');
						$metadado->set_display('no');
						$metadado->set_metadata_type_options($metadata_type_options);
						
					}else if (trim($data[2]) == 'Tainacan\Metadata_Types\Relationship'){
					
						echo "\n","Metadado Relacionamento", "\n";
						
						if (trim($data[3]) == 'Instituição'){
						
							echo "\n","Metadado Relacionamento Instituições", "\n";

							$metadata_type_options = ['collection_id' => $instituicoes_collection->get_id(), 'multiple'=>'yes', 'repeated'=>'yes', 'input_type' => 'tainacan-taxonomy-tag-input', 'search'=>(string)$instituicoes_id->get_id()];
							
							$metadado = new \Tainacan\Entities\Metadatum();
							$metadado->set_collection($insertedCollection);
							$metadado->set_name(trim($data[0]));
							$metadado->set_description($data[1]);
							$metadado->set_metadata_type(trim($data[2]));
							$metadado->set_multiple('yes');
							$metadado->set_status('publish');
							$metadado->set_display('no');
							$metadado->set_metadata_type_options($metadata_type_options);
							
						}else if (trim($data[3]) == 'Responsável'){
						
							echo "\n","Metadado Relacionamento Responsáveis", "\n";
				
							$metadata_type_options = ['collection_id' => $responsaveis_collection->get_id(), 'multiple'=>'yes', 'repeated'=>'yes', 'input_type' => 'tainacan-taxonomy-tag-input', 'search'=>(string)$responsaveis_id->get_id()];
							
							$metadado = new \Tainacan\Entities\Metadatum();
							$metadado->set_collection($insertedCollection);
							$metadado->set_name(trim($data[0]));
							$metadado->set_description($data[1]);
							$metadado->set_metadata_type(trim($data[2]));
							$metadado->set_multiple('yes');
							$metadado->set_status('publish');
							$metadado->set_display('no');
							$metadado->set_metadata_type_options($metadata_type_options);
					}
					
					}else if (trim($data[4]) == 'X'){
						
							echo "\n","Metadado de Texto Multivalorado", "\n";
				
							$metadado = new \Tainacan\Entities\Metadatum();
							$metadado->set_collection($insertedCollection);
							$metadado->set_name(trim($data[0]));
							$metadado->set_description($data[1]);
							$metadado->set_metadata_type(trim($data[2]));
							$metadado->set_status('publish');
							$metadado->set_display('no');
							$metadado->set_multiple('yes');
							$metadado->set_metadata_type_options($metadata_type_options);
					
					}else{
					
						$metadado = new \Tainacan\Entities\Metadatum();
						$metadado->set_collection($insertedCollection);
						$metadado->set_name(trim($data[0]));
						$metadado->set_description($data[1]);
						$metadado->set_metadata_type(trim($data[2]));
						$metadado->set_status('publish');
						$metadado->set_display('no');
					}
				
					if ($metadado->validate()){
						$insertedMetadata = $metadataRepo->insert($metadado);
					} else {
						$erro = $metadado->get_errors();
						var_dump($erro);
					}
			}
				
			}
			
		$cont+=1;
		
		}
		
	fclose($handle);
	
}
	if ($insertedCollection->validate()) {
		$insertedCollection = $collectionsRepo->insert($insertedCollection);
		echo 'Collection created with ID -  ' . $insertedCollection->get_id(), "\n";
	}else {
		$errors = $insertedCollection->get_errors();
	}
	
}else {
	$validationErrors = $collection->get_errors();
	echo $validationErrors;
	die;
}

?>

