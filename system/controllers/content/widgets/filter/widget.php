<?php
class widgetContentFilter extends cmsWidget {

    public $is_cacheable = false;

    public function run(){

        $ctype_name = $this->getOption('ctype_name');

        $core = cmsCore::getInstance();
        $user = cmsUser::getInstance();

        $category = array('id' => 1);

        if (!$ctype_name){

            $ctype = cmsModel::getCachedResult('current_ctype');
            if(!$ctype){ return false; }

            $ctype_name = $ctype['name'];

            if(strpos($core->uri, '.html') === false){

                $current_ctype_category = cmsModel::getCachedResult('current_ctype_category');
                if(!empty($current_ctype_category['id'])){
                    $category = $current_ctype_category;
                }

            } else {

                $item = cmsModel::getCachedResult('current_ctype_item');
                if(!$item){ return false; }

                if(!empty($item['category'])){
                    $category = $item['category'];
                }

            }

            $fields       = cmsModel::getCachedResult('current_ctype_fields');
            $props        = cmsModel::getCachedResult('current_ctype_props');
            $props_fields = cmsModel::getCachedResult('current_ctype_props_fields');

            if($props_fields === null){
                $props_fields = cmsCore::getController('content')->getPropsFields($props);
            }

        } else {

            $content_controller = cmsCore::getController('content');

            $fields = $content_controller->model->getContentFields($ctype_name);
            $props  = $content_controller->model->getContentProps($ctype_name, $category['id']);

            $props_fields = $content_controller->getPropsFields($props);

        }

        if(!$fields && !$props){
            return false;
        }

		$fields_count = 0;

		foreach($fields as $field){
			if ($field['is_in_filter'] && (empty($field['filter_view']) || $user->isInGroups($field['filter_view']))) {
                $fields_count++; break;
            }
		}

		if (!$fields_count && !empty($props_fields)){
			foreach($props as $prop){
				if ($prop['is_in_filter']) { $fields_count++; break; }
			}
		}

		if (!$fields_count){
			return false;
		}

		$filters = array();

		foreach($fields as $name => $field){

			if (!$field['is_in_filter']) { continue; }
			if (!$core->request->has($name)){ continue; }

			$value = $core->request->get($name, false, $field['handler']->getDefaultVarType(true));
			if (!$value) { continue; }

			$filters[$name] = $value;

		}

		if (!empty($props)){
			foreach($props as $prop){

				$name = "p{$prop['id']}";

				if (!$prop['is_in_filter']) { continue; }
				if (!$core->request->has($name)){ continue; }

                $prop['handler'] = $props_fields[$prop['id']];

				$value = $core->request->get($name, false, $prop['handler']->getDefaultVarType(true));
				if (!$value) { continue; }

				$filters[$name] = $value;

			}
		}

        return array(
			'ctype_name'   => $ctype_name,
            'page_url'     => $core->uri_absolute,
            'fields'       => $fields,
            'props_fields' => $props_fields,
            'props'        => $props,
            'filters'      => $filters
        );

    }

}
