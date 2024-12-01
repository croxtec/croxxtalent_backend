<?php
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Cv;
use App\Models\Campaign;
use App\Models\JobInvitation;

if ( ! function_exists('app_url')) {
    function app_url(string $path = '')
    {
        // Url::forceRootUrl(config('myapp.url'));
        $slash = isset($path[0]) && $path[0] == '/' ? '' : '/';
        return config('myapp.url') . $slash . $path;
    }
}

if ( ! function_exists('api_url')) {
    function api_url(string $path)
    {
        $slash = $path[0] == '/' ? '' : '/';
        return config('myapp.api_url') . $slash . $path;
    }
}

if ( ! function_exists('client_url')) {
    function client_url(string $path)
    {
        $slash = $path[0] == '/' ? '' : '/';
        return config('myapp.client_url') . $slash . $path;
    }
}

if ( ! function_exists('cloud_asset')) {

    function cloud_asset(string $path)
    {
        // Storage::url($path);
        $custom_endpoint = config('filesystems.disks.do-spaces.custom_endpoint');
        return $custom_endpoint . '/' . $path;
    }
}

if ( ! function_exists('sign_imageboss_image')) {
    function sign_imageboss_image(string $imageboss_image_url)
    {
        $secret = config('myapp.imageboss_sign_token');

        $image_path = preg_replace("/https:\/\/img.imageboss.me/", '', $imageboss_image_url);

        $boss_token = hash_hmac('sha256', $image_path, $secret);
        return $imageboss_image_url . '?bossToken=' . $boss_token;
    }
}

if ( ! function_exists('image_to_data_url')) {
    function image_to_data_url($image_url)
    {
        try {
            $type = pathinfo($image_url, PATHINFO_EXTENSION);
            $data = file_get_contents($image_url);
            $base64_image = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64_image;
        } catch(ErrorException $e) {
            return null;
        }
    }
}


if ( ! function_exists('related_records_count')) {
    function related_records_count($model_name, $model)
    {
        // $relatedRecordsCount intentially initialized with "1" to avoid accidental
        // deletion of records where function is used.
        $relatedRecordsCount = 0;

        if ($model_name == 'App\Models\Degree') {
            $relatedRecordsCount = 0;
            $relatedRecordsCount += Campaign::where('minimum_degree_id', $model->id)->count();

        } elseif ($model_name == 'App\Models\User') {
            $relatedRecordsCount = 0;
            $relatedRecordsCount += JobInvitation::where('talent_user_id', $model->id)->count();
            $relatedRecordsCount += JobInvitation::where('employer_user_id', $model->id)->count();
            $relatedRecordsCount += Cv::where('user_id', $model->id)->count();

        } elseif ($model_name == 'App\Models\Cv') {
            $relatedRecordsCount = 0;
            $relatedRecordsCount += JobInvitation::where('talent_cv_id', $model->id)->count();

        } elseif ($model_name == 'App\Models\Campaign') {
            $relatedRecordsCount = 0;
            // No restrictions, allow permanent deletion of Campaign

        }

        return $relatedRecordsCount;
    }
}

if (! function_exists('croxxtalent_competency_tree') ){
    function croxxtalent_competency_tree($competency){
        $groups = array();
        $competence_tree = array();

        foreach($competency as $skill){
            $groups[$skill['domain_name']][$skill['core_name']][] = $skill;
        }

        foreach ($groups as $key => $skill) {
           $competence = [
                'name' => $key,
                'core' => []
            ];
            foreach ($skill as $ckey => $core) {
                $score  = array(
                    'name' => $ckey,
                    'skills' => $core
                );
                array_push( $competence['core'], $score );
           }
          array_push($competence_tree, $competence);
        }

        return $competence_tree;
    }
}
