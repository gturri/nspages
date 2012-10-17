/**
 * Add a toolbar button
 */
if(toolbar){
    toolbar[toolbar.length] = {"type":"insert",
                               "title":"nspages",
                               "icon":"../../plugins/nspages/images/tb_nspages.png",
                               "insert":"~~NOCACHE~~ \n<nspages -h1 -subns -exclude:start>"
                              };
}
