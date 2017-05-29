Media Module
============

This module is used for managing media file, such as image, document, video, etc.. This module can be configured as server
media center either at remote web host or local web host, and it only provides service APIs and media management, you need to
develop your uploading page for user to choose file and execute upload.

Samples of using media helpers into templates here :
usr/module/media/template/admin/test-index.phtml

More resize commands here :
https://github.com/tck/zf2-imageresizer#command-list

Activate grayscaling - For testing / find usages of old media system into templates : 
Your non migrated images - e.g.e using old method instead of new media helpers - will be White&Black
You can use for that purpose one of these 2 methods : 
- Add SetEnv TEST_MEDIA 1 into your www htaccess
- Or create (empty) file at www directory name "MEDIA_TEST_FLAG" without extension

If Jquery Ui from Pi Core is updated, custom jquery ui from this module must be also updated with only sortable component and dependencies :
()widget.js, data.js, scroll-parent.js, widgets/sortable.js, widgets/mouse.js)

Media Helper, we have to cast the output to String, with echo function or caster as follow :
<?php $shareImage = (string) Pi::api('doc','media')->getSingleLinkUrl($story['main_image'])->thumb(800, 600); ?>
<?php Pi::api('doc','media')->getSingleLinkUrl($story['main_image'])->thumb(800, 600); ?>