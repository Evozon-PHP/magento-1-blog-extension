/**
 * Tag management class
 * It is used for the adminhtml_blog_post_edit action
 * It loads the autocompleter
 * Allows the user to add new tags to database from Tags tab in post edit
 * Doesn`t allow to add the same tag for the post
 * Allows user to remove a tag from the database
 * 
 * @author Dana Negrescu <dana.negrescu@evozon.com>
 * @type prototype
 */

var Tag = Class.create();

Tag.prototype = {
    initialize: function (url, id, initialSelectedTags) {
        var selectedTagsIds = new Array();

        var xhr;
        this.selectedTags = JSON.parse(initialSelectedTags);
        var tagClass = this;
        new autoComplete({
            selector: 'input[name="selector-tag"]',
            minChars: 2,
            delay: 50,
            cache: 0,

            source: function (term, response) {
                try {
                    xhr.abort();
                } catch (e) {
                }

                xhr = new Ajax.Request(url, {
                    method: 'post',
                    requestHeaders: {Accept: 'application/json'},
                    parameters: {
                        term: term.toLowerCase(),
                        store: id
                    },
                    onSuccess: function (transport) {
                        var parsed = transport.responseText.evalJSON(true);
                        var filteredItems = parsed.filter(function(item){
                            return !(item[0] in tagClass.getSelectedTags())
                        });

                        var arr = Object.keys(filteredItems).map(function (k) {
                            return filteredItems[k];
                        });
                        response(arr);
                    }
                });
            },
            renderItem: function (item, search) {
                search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");
                return '<div class="autocomplete-suggestion" data-tag-value="' + item[1] +
                        '" data-tag-id="' + item[0] + '" data-tag-count="' + item[2] +
                        '" data-val="' + item[1] + '">' + item[1].replace(re, "<b>$1</b>") + '</div>';
            },
            onSelect: function (e, term, item) {
                $('add-tag').dataset.tagId = item.getAttribute('data-tag-id');
                $('add-tag').dataset.tagValue = item.getAttribute('data-tag-value');
                $('add-tag').dataset.tagCount = item.getAttribute('data-tag-count');

                tagClass.validateTagContext();
            }
        });
    },
    setSelectedTags: function(selectedTags)
    {
        this.selectedTags = selectedTags;
        Object.keys(selectedTags).toString();
        $('selected-tags-ids').value = Object.keys(selectedTags).toString();
    },
    getSelectedTags: function()
    {
        return this.selectedTags;
    },
    addTagToList: function ()
    {
        //if tag is not duplicated we add id
        var value = $('add-tag').dataset.tagValue;
        var id = $('add-tag').dataset.tagId;
        var count = $('add-tag').dataset.tagCount;

        var tag = '<li class="tag-show-details" data-id="' + id + '" id="selected-tag-' + id
                + '"><span class="tag-pill">'
                + '<span class="tag-count">' + count + '</span>'
                + '<span class="tag-name">' + value + '</span></span>'
                + '<a onclick="tag.removeTagFromList(' + id + ')" class="tag-remove">x</a></li>';

        this.emptyInput();

        var selectedTag = {
            "id": id,
            "name": value
        };

        this.updateSelectedTags('add', selectedTag);
        if ($('succes-msg').style.display != "none") {
            $('succes-msg').style.display = "none";
        }

        $('tag-list').insert(tag);
        $('add-tag-button').disabled = true;
    },
    emptyInput: function ()
    {
        $('add-tag').value = '';
        this.resetSearchData();
    },
    removeTagFromList: function (id)
    {
        $('selected-tag-' + id).remove();
        var selectedTag = {
            "id": id,
        };
        this.updateSelectedTags('remove', selectedTag);
    },
    updateSelectedTags: function (event, selectedTag)
    {
        var selectedTags = this.getSelectedTags();
        if (event === 'add') {
            selectedTags[selectedTag.id] = {
                "entity_id": selectedTag.id,
                'name': selectedTag.name
            };
        } else {
            delete selectedTags[selectedTag.id];
        }
        this.setSelectedTags(selectedTags);
    },
    resetSearchData: function ()
    {
        if ($('selected-tags-error').style.display != "none") {
            $('selected-tags-error').style.display = "none";
        }

        $('add-tag').dataset.tagId = '';
        $('add-tag').dataset.tagValue = '';
        $('add-tag').dataset.tagCount = '0';
    },
    validateTagContext: function (storeId)
    {
        //there are two variations: the tag has been selected from the autocomplete or the tag has to be added to the database first
        //if the user added a new tag, the add form for a tag has to be rendered
        if (this.tagIsNew() === true)
        {
            return this.showNewTagForm(storeId);
        }
        if (this.isDuplicate() === true) {
            $('selected-tags-error').style.display = "block";
            return $("add-tag").focus();
        } else {
            return this.addTagToList();
        }
    },
    tagIsNew: function ()
    {
        var id = $('add-tag').dataset.tagId;
        if (id === "") {
            return true;
        }

        return false;
    },
    isDuplicate: function ()
    {
        var tagId = $('add-tag').dataset.tagId;

        return (tagId in this.getSelectedTags());
    },
    isTagNameAddedOnPost: function(name) {
        var selectedTags = this.getSelectedTags();
        for (var tagId in selectedTags) {
            if (selectedTags[tagId].name == name) {
                return true;
            }
        }
        return false;
    },
    showNewTagForm: function (storeId)
    {
        this.showOrHideBlock('add-new-tag');
        this.showOrHideBlock('add-tag-button');
        $$('input[name="name[' + storeId + ']"]')[0].value = $('add-tag').value;
    },
    showOrHideBlock: function (block) {
        $(block).toggle();
    },
    saveTag: function (id, url)
    {
        this.removeErrors();

        var data = new Hash();
        $$('.input-tag').each(function (item) {
            store = item.dataset.store;
            data.set(store, item.value);
        });

        new Ajax.Request(url, {
            method: 'post',
            parameters: {
                'tag': data.toQueryString()
            },
            onSuccess: function (transport) {
                if (transport.responseText.isJSON() === true) {

                    var response = transport.responseText.evalJSON();

                    if (response.succes) {
                        $('add-tag').value = data.get(id);
                        $('add-tag').dataset.tagValue = data.get(id);
                        $('add-tag').dataset.tagId = response.id;
                        $('add-tag').dataset.tagCount = 0;

                        this.clearForm();
                        this.removeErrors();
                        this.showOrHideBlock('add-new-tag');
                        this.showOrHideBlock('add-tag-button');
                        this.showOrHideBlock('succes-msg');
                        this.addTagToList();
                    }

                    if (response.error) {              
                        response.error.each(function (message) {
                            if (Array.isArray(message) || typeof(message) === 'string') {
                                var errorMsg = '<li class="error-msg"><ul><li>' + message
                                    + '</li></ul></li>';
                                $('ajax-error-msg').insert(errorMsg);
                            } else {
                                $H(message).each(function (pair) {
                                    var errorMsg = '<li class="error-msg"><ul><li>' + pair.value
                                    + '</li></ul></li>';
                                    $('ajax-error-msg').insert(errorMsg);
                                });
                            }
                        });
                    }
                }
            }.bind(this)
        });
    },
    removeErrors: function ()
    {
        $('ajax-error-msg').update("");
    },
    clearForm: function ()
    {
        $$('.input-tag').each(function (item) {
            item.clear();
        });
    },
    cancelTag: function ()
    {
        this.showOrHideBlock('add-new-tag');
        this.showOrHideBlock('add-tag-button');
        this.clearForm();
        this.removeErrors();
        this.resetSearchData();
        $('add-tag').value = '';
        $('add-tag').focus();
        $('add-tag-button').disabled = true;
    }
};