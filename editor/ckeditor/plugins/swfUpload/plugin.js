CKEDITOR.plugins.add('swfUpload',
{
    init: function(editor)    
    {        
        var pluginName = 'swfUpload';        
        CKEDITOR.dialog.add(pluginName, this.path + 'swfUpload.js');        
        editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
        editor.ui.addButton(pluginName,
        {               
            label: editor.lang.swfUpload.pluginTitle,
            command: pluginName
        });
    }
});
