CKEDITOR.plugins.add('flvPlayer',
{
    init: function(editor)    
    {        
        var pluginName = 'flvPlayer';        
        CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/flvPlayer.js');        
        editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
        editor.ui.addButton(pluginName,
        {               
            label: editor.lang.flvPlayer.pluginTitle,
            command: pluginName
        });
    }
});
