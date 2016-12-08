<?php

class onPhotosContentAlbumsItemsHtml extends cmsAction {

    public function run($data){

        list($type, $ctype, $profile, $current_folder) = $data;

        if($type == 'user_view'){
            return $this->getUserViewHtml($ctype, $profile, $current_folder);
        }

        return false;

    }

    private function getUserViewHtml($ctype, $profile, $current_folder) {

        $this->model->orderBy($this->options['ordering'], 'desc');

        if (cmsUser::isAllowed('albums', 'view_all') || $this->cms_user->id == $profile['user_id']) {
            $this->model->disablePrivacyFilter();
        }

        $profile['url_params'] = array('photo_page' => 1);
        $profile['base_url']   = href_to('users', $profile['id'], array('content', $ctype['name']));

        return $this->renderPhotosList($profile, 'user_id', $this->cms_core->request->get('photo_page', 1));

    }

}
