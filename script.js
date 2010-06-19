/* Add Note buttons to the toolbar */
/* from http://wiki.splitbrain.org/wiki:tips:toolbarbutton */

if(toolbar){ 
    toolbar[toolbar.length] = {"type":"insert", 
                               "title":"nspages",
                               "icon":"../../plugins/nspages/images/tb_nspages.png", 
                               "insert":"~~NOCACHE~~ \n<nspages -simplelist -h1 -subns -exclude:start>"
                              }; 
}
