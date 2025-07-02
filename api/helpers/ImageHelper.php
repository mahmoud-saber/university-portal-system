<?php

namespace api\helpers;

use Yii;
use yii\base\InvalidParamException;

class ImageHelper
{

    public static function ImageFromUrl($url, $upPath = 'profile')
    {
        $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $userImage = 'img-' . md5(uniqid(rand(), 1)) . "." . 'jpeg';
        $thumb_image = file_get_contents($url);
        if ($http_response_header != null) {
            $thumb_file = $path . $userImage;
            file_put_contents($thumb_file, $thumb_image);
        }
        return $userImage;
    }

    public static function ImageFromBinary($binary, $upPath = 'profile')
    {

        $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $imageInfo = explode(";base64,", $binary);
        $imgExt = str_replace('data:image/', '', $imageInfo[0]);
        $image = str_replace(' ', '+', $imageInfo[1]);
        $imageName = "img-" . time() . "." . $imgExt;

        $imagePath = $path . '/' . $imageName;

        file_put_contents($imagePath, $image);

        return $imageName;
    }

    public static function Base64Image($base64_image_string, $upPath = 'profile')
    {

        $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // data is like: data:image/png;base64,asdfasdfasdf
        $splited = explode(',', substr($base64_image_string, 5), 2);
        $mime = $splited[0];
        $data = $splited[1];
        $mime_split_without_base64 = explode(';', $mime, 2);
        $mime_split = explode('/', $mime_split_without_base64[0], 2);
        $validExtensions = ['png', 'jpeg', 'jpg'];
        if (in_array($mime_split[1], $validExtensions)) {
            if (count($mime_split) == 2) {
                $extension = $mime_split[1];
                if ($extension == 'jpeg') {
                    $extension = 'jpg';
                }

                $output_file_with_extension = 'IMG_' . (time() + rand(0, 10000)) . '.' . $extension;
            }
            $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath . '/';
            file_put_contents($path . $output_file_with_extension, base64_decode($data));
            return $output_file_with_extension;
        } else {
            throw new InvalidParamException('Wrong File type.');
        }
    }


    public static function delete_files($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                ImageHelper::delete_files($file);
            }

            rmdir($target);
        } elseif (is_file($target)) {
            unlink($target);
        }
    }

    public static function Base64IMageConverter($binary, $upPath = 'profile')
    {

        $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $imageName = "img-" . (intval(time()) + rand(1, 100000)) . ".jpeg";

        $directory = $path . '/' . $imageName;

        $entry = base64_decode($binary);
        $image = imagecreatefromstring($entry);

        header('Content-type:image/jpeg');

        imagejpeg($image, $directory);

        imagedestroy($image);

        if (file_exists($directory)) {
            return $imageName;
        } else {
            return false;
        }
    }

    public static function Base64IPdfConverter($pdf_content, $upPath = 'profile')
    {

        $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath;
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $FileName = "file-" . (intval(time()) + rand(1, 100000)) . ".pdf";

        $directory = $path . '/' . $FileName;
        $data = substr($pdf_content, strpos($pdf_content, ',') + 1);
        $data = str_replace(' ', '+', $data);
        $pdf_decoded = base64_decode($data);
        $pdf = fopen($directory, 'w');

        fwrite($pdf, $pdf_decoded);
        fclose($pdf);

        if (file_exists($directory)) {
            return $FileName;
        } else {
            return false;
        }
    }

    public static function Base64File($file, $upPath = 'profile', $allowedTypes = ['docx', 'doc', 'pdf', 'jpeg', 'jpg', 'png'])
    {
        if (preg_match('/^data:@file\/(\w+);base64,/', $file, $type)) {
            $data = substr($file, strpos($file, ',') + 1);
            $type = strtolower($type[1]); // $type[1] matches values in first text in parenthesized =>(\w+)

            if (!in_array($type, $allowedTypes)) {
                return ResponseHelper::sendFailedResponse(['error' => Yii::t('common', 'Invalid type')]);
            }

            $data = str_replace(' ', '+', $data);
            $data = base64_decode($data);
            $filePath = Yii::getAlias('@storage') . '/web/source/' . $upPath;
            if (!file_exists($filePath)) {
                mkdir($filePath, 0755, true);
            }
            $file_name = Yii::$app->getSecurity()->generateRandomString() . "." . $type;
            file_put_contents($filePath . '/' . $file_name, $data);
            return $file_name;
        }

        throw new InvalidParamException('Wrong File type.');
    }

    public static function Base64FileUpload($file, $upPath = 'profile', $allowedTypes = ['docx', 'doc', 'pdf', 'jpeg', 'jpg', 'png'])
    {

        $splited = explode(',', substr($file, 5), 2);
        $mime = $splited[0];
        $data = $splited[1];
        $mime_split_without_base64 = explode(';', $mime, 2);
        $mime_split = explode('/', $mime_split_without_base64[0], 2);

        if (in_array($mime_split[1], $allowedTypes)) {
            if (count($mime_split) == 2) {
                $extension = $mime_split[1];
                if ($extension == 'jpeg') {
                    $extension = 'jpg';
                }

                if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png') {
                    $fileType = 'IMG_';
                } elseif ($extension == 'docx' || $extension == 'doc') {
                    $fileType = 'DOC_';
                } elseif ($extension == 'pdf') {
                    $fileType = 'PDF_';
                }

                $output_file_with_extension = $fileType . (time() + rand(0, 10000)) . '.' . $extension;
            }

            $filePath = Yii::getAlias('@storage') . '/web/source/' . $upPath;
            if (!file_exists($filePath)) {
                mkdir($filePath, 0755, true);
            }

            $path = \Yii::getAlias('@storage') . '/web/source/' . $upPath . '/';
            file_put_contents($path . $output_file_with_extension, base64_decode($data));
            return $output_file_with_extension;
        }

        return ResponseHelper::sendFailedResponse(['error' => Yii::t('common', 'did not match data URI with image data')], 400);
    }

    public static function uploadImageOrFile($file, $upPath, $size = 20000000) //20000000 20 MiB
    {
        $filename = basename($file["name"]);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $allowedExt = ['jpeg', 'png', 'jpg', 'pdf'];
        $ext = end(explode('.', $filename));

        if (!empty($file) && in_array($file['type'], $allowedTypes) && in_array(strtolower($ext), $allowedExt) && $file['size'] <= $size) {
            $rand = Yii::$app->security->generateRandomString();
            $filename =  $rand . '.' . $ext;
            $target_file = $upPath . $filename;
            if (!file_exists($upPath)) {
                mkdir($upPath, 0775, true);
            }
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return $filename;
            }
        } else {
            throw new InvalidParamException('Wrong File type.');
        }
    }
}
