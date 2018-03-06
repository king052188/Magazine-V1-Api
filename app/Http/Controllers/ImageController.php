<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Intervention\Image\ImageManager;



class ImageController extends Controller
{
    //
    public static $storage_img  = "/Users/kingpauloaquino/PhpstormProjects/UploaderSite/Client/Contract/";
    public static $replaced  = "/Users/kingpauloaquino/PhpstormProjects/UploaderSite/Client/"; ///Users/kingpauloaquino/PhpstormProjects/UploaderSite/Client/Contract
    public static $replacement  = "http://contract.kpa21.com/";

    public function full_path($dir_name) {
        $path = $this::$storage_img . $dir_name;
        return $path;
    }

    public function get_contract_cloud($dir_name)
    {
        KPAHelper::set_access_control_allow_origin();

        $sub_files = $this->full_path( $dir_name );

        $img_list = \File::files($sub_files);

        if(count($img_list) <= 0) {
            $lists = array(
                "code" => 404,
                "message" => "No record.",
                "count" => 0,
                "contracts" => []
            );

            return $lists;
        }

        for($m = 0; $m < count($img_list); $m++) {
            $img_file = str_replace(
                $this::$replaced,
                $this::$replacement,
                $img_list[$m]
            );
            $details[] = array(
                "img_enhanced" => true,
                "img_name" => basename($img_list[$m]),
                "img_url" =>  $img_file
            );
        }

        if(count($details) > 0) {
            $lists = array(
                "code" => 200,
                "message" => "Success.",
                "count" => count($details),
                "contracts" => $details
            );
        }

        return $lists;
    }

    public function resize_image_logo() {

        // create an image manager instance with favored driver
        $manager = new ImageManager(array('driver' => 'imagick'));

        $img = $manager->make('public/paulo.jpg')->resize(300, 100);

        return $img;
    }
}
