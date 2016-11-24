/**
 * Widget javascript file;
 * loads gallery widget; shows selected images; updates the selected images list according to user selection;
 * 
 * @author Dana Negrescu <dana.negrescu@evozon.com>
 * @type prototype
 */
var galleryTools = {
    getDivHtml: function (id, html) {
        if (!html)
            html = '';
        return '<div id="' + id + '">' + html + '</div>';
    },
    onAjaxSuccess: function (transport) {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON()
            if (response.error) {
                throw response;
            } else if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
        }
    },
    openDialog: function (widgetUrl) {
        if ($('widget_window') && typeof (Windows) != 'undefined') {
            Windows.focus('widget_window');
            return;
        }

        this.dialogWindow = Dialog.info(null, {
            draggable: true,
            resizable: false,
            closable: true,
            className: 'magento',
            windowClassName: "popup-window",
            title: Translator.translate('Insert Gallery...'),
            top: 50,
            width: 950,
            //height:450,
            zIndex: 1000,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: 'widget_window',
            onClose: this.closeDialog.bind(this)
        });

        new Ajax.Updater('modal_dialog_message', widgetUrl, {evalScripts: true});
    },
    closeDialog: function (window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            selectedImages.length=0;
            window.close();
        }
    }
}

var GalleryWidget = {};
GalleryWidget.Widget = Class.create();
GalleryWidget.Widget.prototype = {
    initialize: function (formEl, galleryEl, widgetOptionsEl, fieldsUrl, widgetTargetId, widgetPostId, widgetStoreId) {

        $(formEl).insert({bottom: galleryTools.getDivHtml(widgetOptionsEl)});

        this.formEl = formEl;
        this.widgetValue = galleryEl;
        this.widgetOptionsEl = $(widgetOptionsEl);
        this.fieldsUrl = fieldsUrl;
        this.widgetTargetId = widgetTargetId;
        this.widgetPostId = widgetPostId;
        this.widgetStoreId = widgetStoreId;

        this.loadFields();
    },
    loadFields: function () {
        var optionsContainerId = this.widgetOptionsEl.id + '_' + this.widgetValue.gsub(/\//, '_');

        var params = {widget_type: this.widgetValue, values: {store: this.widgetStoreId, post: this.widgetPostId}};
        new Ajax.Request(this.fieldsUrl,
                {
                    parameters: {widget: Object.toJSON(params)},
                    onSuccess: function (transport) {
                        try {
                            galleryTools.onAjaxSuccess(transport);
                            this.widgetOptionsEl.insert({bottom: galleryTools.getDivHtml(optionsContainerId, transport.responseText)});
                        } catch (e) {
                            alert(e.message);
                        }
                    }.bind(this)
                }
        );
    },
    insertWidget: function () {
        widgetOptionsForm = new varienForm(this.formEl);
        if (widgetOptionsForm.validator && widgetOptionsForm.validator.validate() || !widgetOptionsForm.validator) {
            var formElements = [];
            var i = 0;
            Form.getElements($(this.formEl)).each(function (e) {
                if (!e.hasClassName('skip-submit')) {
                    formElements[i] = e;
                    i++;
                }
            });

            // Add as_is flag to parameters if wysiwyg editor doesn't exist
            var params = Form.serializeElements(formElements);
            if (!this.wysiwygExists()) {
                params = params + '&as_is=1';
            }
            new Ajax.Request($(this.formEl).action,
                    {
                        parameters: params,
                        onComplete: function (transport) {
                            try {
                                galleryTools.onAjaxSuccess(transport);
                                Windows.close("widget_window");

                                if (typeof (tinyMCE) != "undefined" && tinyMCE.activeEditor) {
                                    tinyMCE.activeEditor.focus();
                                    if (this.bMark) {
                                        tinyMCE.activeEditor.selection.moveToBookmark(this.bMark);
                                    }
                                }

                                this.updateContent(transport.responseText);
                            } catch (e) {
                                alert(e.message);
                            }
                        }.bind(this)
                    });
        }
    },
    updateContent: function (content) {
        if (this.wysiwygExists()) {
            this.getWysiwyg().execCommand("mceInsertContent", false, content);
        } else {
            var textarea = document.getElementById(this.widgetTargetId);
            updateElementAtCursor(textarea, content);
            varienGlobalEvents.fireEvent('tinymceChange');
        }
    },
    wysiwygExists: function () {
        return (typeof tinyMCE != 'undefined') && tinyMCE.get(this.widgetTargetId);
    },
    getWysiwyg: function () {
        return tinyMCE.activeEditor;
    },
    getWysiwygNode: function () {
        return tinyMCE.activeEditor.selection.getNode();
    },
    resetInputs: function(form) {
        var form = $(form);
        form.reset();
        form.select('input #gallery-images').each(
          function(input) {
            input.clear();
          }
        );
        return form;
    },
}

selectedImages = new Array();

var galleryImages =  {
    updateList: function (image) {
        selectedImages = $('gallery-images').value.split(',');
        var imageId = image.readAttribute('ref');

        image.writeAttribute('checked', !image.readAttribute('checked'));
        if (image.readAttribute('checked') == 'checked') {
            selectedImages.push(imageId);
            image.setStyle({'border': 'solid', 'border-color': 'orange'});
        } else {
            imageIndex = selectedImages.indexOf(imageId);
            if (imageIndex != -1) {
                selectedImages.splice(imageIndex, 1);
                image.setStyle({'border': 'none'});
            }
        }

        $('gallery-images').value = selectedImages.toString();
    }
}