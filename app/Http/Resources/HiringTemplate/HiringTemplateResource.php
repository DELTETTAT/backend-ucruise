<?php

namespace App\Http\Resources\HiringTemplate;

use Illuminate\Http\Resources\Json\JsonResource;

class HiringTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    // public function toArray($request)
    // {
    //     return [
    //         'id' => $this->id,
    //         'title' => $this->title,
    //         'status' => $this->status,
    //         'header_image' => isset($this->header_image) ? asset('hiringtemplate/header/' . $this->header_image) : null,
    //         'background_image' => isset($this->background_image) ? asset('hiringtemplate/background/' . $this->background_image) : null,
    //         'watermark' => isset($this->watermark) ? asset('hiringtemplate/watermark/' . $this->watermark) : null,
    //         'footer_image' => isset($this->footer_image) ? asset('hiringtemplate/footer/' . $this->footer_image) : null,
    //         'content' => $this->content,
    //         'template_name' => $this->template_name,
    //         'template_type' => $this->template_type,
    //         'watermarkOpacity' => $this->watermarkOpacity,
    //         'watermarkPosition' => $this->watermarkPosition,
    //         'headerImagePosition' => $this->headerImagePosition,
    //         'footerImagePosition' => $this->footerImagePosition,
    //         'header_image_scale' => $this->header_image_scale,
    //         'footer_image_scale' => $this->footer_image_scale,
    //     ];
    // }

    public function toArray($request){
       
            $iconFiles = isset($this->icon_files) ? json_decode($this->icon_files) : json_decode('[]');
            $iconUrls = array_map(function ($file) {
                return asset('hiringtemplate/watermark/' . $file);
            }, $iconFiles);
         
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'header_image' => isset($this->header_image) ? asset('hiringtemplate/header/' . $this->header_image) : null,
            'background_image' => isset($this->background_image) ? asset('hiringtemplate/background/' . $this->background_image) : null,
            'watermark' => isset($this->watermark) ? asset('hiringtemplate/watermark/' . $this->watermark) : null,
            'footer_image' => isset($this->footer_image) ? asset('hiringtemplate/footer/' . $this->footer_image) : null,
            'content' => $this->content,
            'template_name' => $this->template_name,
            'template_type' => $this->template_type,
            'watermarkOpacity' => $this->watermarkOpacity,
            'watermarkPosition' => $this->watermarkPosition,
            'headerImagePosition' => $this->headerImagePosition,
            'footerImagePosition' => $this->footerImagePosition,
            'headerImagePosition' => $this->headerImagePosition,
            'icon_files' =>  $iconUrls, // json_decode($this->icon_files),
            'icon_positions' => isset($this->icon_positions) ? json_decode($this->icon_positions) : json_decode('[]'), //    json_decode($this->icon_positions),
        ];
    }
}
