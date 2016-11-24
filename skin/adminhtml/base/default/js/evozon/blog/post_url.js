/**
 * System Config: select post url type. 
 * Set default value 2 for all checkboxes, but set 1 only for current clicked checkbox.
 * 
 * @param Object object
 * @returns void
 */
function postUrlSelect(object) {
    // get object id
    var objectId = $(object.id);
    
    // set all checkboxes with value 2,
    $$('input[rel="post-url"]').each( function(box){ box.value = 2; box.checked = false; } );
    
    // make selected checkboxes checked and with value of 1
    objectId.checked = true;
    objectId.value = 1;
    
    // set dataPattern on custom structure input
    var dataPattern = objectId.getAttribute('data-pattern');
    $('evozon_blog_post_post_url_url_custom').value = dataPattern;
}
window.addEventListener("load", function() {
    $$('input[rel="post-url"]').each( function(box){ 
        if(box.value == 1) { 
            box.checked = true; 
            //$('evozon_blog_post_post_url_url_custom').value = box.getAttribute('data-pattern');
        } 
    });
});