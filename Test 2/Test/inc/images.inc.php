<?php

class ImageHandler
{
    public $save_dir;
    public $max_dims;

    public function __construct($save_dir, $max_dims=array(350,240))
    {
        $this->save_dir = $save_dir;
        $this->max_dims = $max_dims;
    }

    public function processUploadedImage($file, $rename=TRUE)
    {
        list($name,$type,$tmp,$err,$size)=array_values($file);

        if($err != UPLOAD_ERR_OK){
            throw new Exception('An error occurred with the upload!');
            return;
        }
        $this->doImageResize($tmp);
        $this->checkSaveDir();
        //rename file is flag true
        if($rename===TRUE){
            $img_ext = $this->getImageExtension($type);
            $name = $this->renameFile($img_ext);
        }
        //create filepath
        $filepath = $this->save_dir . $name;
        $absolute = $_SERVER['DOCUMENT_ROOT'] . $filepath;
        //save image
        if(!move_uploaded_file($tmp, $absolute)){
            throw new Exception("Couldn't save the uploaded file!");
        }
        return $filepath;
    }

    private function renameFile($ext)
    {
        return time() . '_' . mt_rand(1000,9999) . $ext;
    }

    private function getImageExtension($type)
    {
        switch($type){
            case 'image/gif';
                return '.gif';
            case 'image/jpeg';
            case 'image/pjpeg';
                return '.jpg';
            case 'image/png';
                return '.png';
            default:
                throw new Exception('File type is not recognized!');
        }
    }

    private function checkSaveDir()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . $this->save_dir;

        if(!is_dir($path)){
            if(!mkdir($path,0777,TRUE)){
                throw new Exception("Can't create the directory!");
            }
        }
    }

    private function getNewDims($img)
    {
        list($src_w, $src_h) = getimagesize($img);
        list($max_w, $max_h) = $this->max_dims;

        if($src_w > $max_w || $src_h > $max_h){
            $s = min($max_w/$src_w,$max_h/$src_h);
        } else {
            $s=1;
        }
        $new_w = round($src_w * $s);
        $new_h = round($src_h * $s);
        return array($new_w, $new_h, $src_w, $src_h);
    }

    private function getImageFunctions($img)
    {
        $info = getimagesize($img);
        switch($info['mime']) {
            case 'image/jpeg';
            case 'image/pjpeg':
                return array('imagecreatefromjpeg', 'imagejpeg');
                break;
            case 'image/gif':
                return array('imagecreatefromgif','imagegif');
                break;
            case 'image/png':
                return array('imagecreatefrompng','imagepng');
                break;
            default:
                return FALSE;
                break;
        }
    }

    private function doImageResize($img)
    {
        $d = $this->getNewDims($img);
        $funcs = $this->getImageFunctions($img);

        $src_img = $funcs[0]($img);
        $new_img = imagecreatetruecolor($d[0],$d[1]);

        if(imagecopyresampled($new_img,$src_img,0,0,0,0,$d[0],$d[1],$d[2],$d[3])){
            imagedestroy($src_img);
            if($new_img && $funcs[1]($new_img,$img)){
                imagedestroy($new_img);
            } else {
                throw new Exception('Failed to save the new image!');
            }
        } else {
            throw new Exception('Could not resample the image!');
        }
    }
}
?>

