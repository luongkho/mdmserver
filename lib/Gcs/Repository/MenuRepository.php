<?php

/**
 * Description: class for Menus service
 * Getting title of menu
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Repository;

class MenuRepository {

    /**
     * Get title of menu
     * @param String $current_module
     * @return boolean | String
     */
    public function getTitleMenu($current_module) {
        $menus = \sfConfig::get("menu_left_data");
        foreach ($menus as $menu) {
            $submenu = $menu['submenu'];
            if (!empty($menu['submenu'])) {
                foreach ($submenu as $sub) {
                    if ($sub['name'] == $current_module) {
                        return $sub['title'];
                    }
                }
            }
        }
        return false;
    }

}
