# -*- coding: utf-8 -*-
"""
Created on Tue Jan 15 09:45:27 2019

@author: Luis
"""

import glob
import csv

img_names = []
list_img = []

links_dict = {}

jpg_imgs = glob.glob('D:\Museu Antropologico\*jpg')

for name in jpg_imgs:
    img_names.append(name.strip("D:\Museu Antropologico"))


for img in img_names:
    if " " in img:
        img_id = img.split(" ")
        list_img.append(img_id[0])
    else:
        list_img.append(img.strip('.JPG'))
        
list_img = set(list_img)

for item in list_img:
    links_dict[item] = []
    
    for image in img_names:
        
        img_name = image.strip(".JPG")
        
        
        if " " in img_name:
            img_name = img_name.split(" ")
            img_name = img_name[0]
        
        if item == img_name:
           links_dict[item].append(".y/"+image)
with open('link_imgs_MA.csv', 'w', encoding = "utf-8", newline='') as out_file:
    
    writer = csv.writer(out_file, quoting=csv.QUOTE_MINIMAL)
    writer.writerow(['item','special_document','special_attachment'])
    
    print(len(list_img))
    for item in list_img:
        
        if len(links_dict[item]) >= 2:
            writer.writerow([item,links_dict[item][0],"||".join(links_dict[item][1:])])
        else:
            writer.writerow([item,links_dict[item][0],""])
