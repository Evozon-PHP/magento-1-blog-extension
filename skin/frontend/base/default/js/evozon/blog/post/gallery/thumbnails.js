var BlogPostGalleryManager = {
    swapGalleryImage: function (targetImage, id) {
        this.id = id;
        this.targetImage = $(targetImage);
        
        imageGallery = $(this.id).select('div.blog-post-gallery');
        imageGallery.each(function (el) {
            var images = el.select('img.blog-post-gallery-image');
            images.each(function (image) {
                image.removeClassName('visible');
            });
        });

        $(this.targetImage.id).addClassName('visible');
    },
    wireGalleryThumbnails: function () {
        $$('.blog-post-gallery-thumbs .gallery-thumb-link').each(function (item) {
            item.observe('click', (function (el) {
                this.el = $(item);
                var id = this.el.dataset.galleryImageIndex.split('-')[0];
                target = $$('#gallery-image-' + this.id + this.el.dataset.galleryImageIndex);
                BlogPostGalleryManager.swapGalleryImage(target[0], id);
            }));
        });
    },
    init: function () {
        BlogPostGalleryManager.wireGalleryThumbnails();
    }
};