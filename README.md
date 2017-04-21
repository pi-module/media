Media Module
============

This module is used for managing media file, such as image, document, video, etc.. This module can be configured as server
media center either at remote web host or local web host, and it only provides service APIs and media management, you need to
develop your uploading page for user to choose file and execute upload.

Samples of using media helpers into templates here :
usr/module/media/template/admin/test-index.phtml

More resize commands here :
https://github.com/tck/zf2-imageresizer#command-list

For testing / find usages of old media system into template :
- Add SetEnv TEST_MEDIA 1 into your www htaccess
- Or create (empty) file at www directory name "MEDIA_TEST_FLAG" without extension