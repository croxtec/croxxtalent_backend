<?php

namespace App\Helpers;

use App\Models\Skill;
use App\Models\SkillSecondary as Secondary;
use App\Models\SkillTertiary as Tertiary;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use  App\Models\Industry;

class SkillImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(isset($row['primary_skill'])){
            $skill = Skill::where('name',$row['primary_skill'])->first();
            $industry = Industry::where( 'name', $row['industry'] )->first();
            // info([ 'Industry', $industry ]);
            if(!$skill && $industry){
                $skill = new Skill();
                $skill->industry_id = $industry->id;
                $skill->name =  $row['primary_skill'];
                $skill->description =  $row['description_primary_skill'];
                $skill->save();
            }
            if($industry) $skill->industry_id = $industry->id;
            $skill->description =  $row['description_primary_skill'];
            $skill->save();
            // Secondary Skill
            if(isset($row['secondary_skill'])){
                $secondary = Secondary::where(['name' => $row['secondary_skill'], 'skill_id' => $skill->id])->first();
                if(!$secondary){
                    $secondary = new Secondary();
                    $secondary->name =  $row['secondary_skill'];
                    $secondary->skill_id = $skill->id;
                    $secondary->description =  $row['description_secondary_skill'];
                    $secondary->save();
                }
                $secondary->description =  $row['description_secondary_skill'];
                $secondary->save();
            }
            // Tertiary Skill
            if(isset($row['tertiary_skill'])){
                // Log::info('Done', $row);
                $tertiary = Tertiary::where('name', $row['tertiary_skill'])->where('skill_secondary_id' , $secondary->id)->first();
                if(!$tertiary){
                    $tertiary = new Tertiary();
                    $tertiary->name =  $row['tertiary_skill'];
                    $tertiary->skill_id = $skill->id;
                    $tertiary->skill_secondary_id = $secondary->id;
                    $tertiary->description =  $row['description_tertiary_skill'];
                    $tertiary->save();
                }
                $tertiary->description =  $row['description_tertiary_skill'];
                $tertiary->save();
            }
            return Skill::find($skill->id);
        }
    }

    public function old_model($row){
        if(isset($row['primary'])){
            $skill = Skill::where('name',$row['primary'])->first();
            if($skill){
                $row['id'] = $skill->id;
                $secondary = Secondary::where(['name' => $row['name'], 'skill_id' => $row['id']])->first();
               if($secondary){
                   $secondary->name =  $row['name'];
                   $secondary->description =  $row['description'];
                   $secondary->save();
                   return Secondary::find($secondary->id);
               }else{
                    $row['skill_id'] = $skill->id;
                    unset($row['primary']);  unset($row['id']);
                    return Secondary::firstOrCreate($row);
               }
            }else{
                return Secondary::find(1);
            }
        }else if(isset($row['secondary'])){
            $secondary = Secondary::where('name',$row['secondary'])->first();
            if($secondary){
                $row['id'] = $secondary->id;
                $tertiary = Tertiary::where('name', $row['name'])->where('skill_secondary_id' , $row['id'])->first();
                if($tertiary){
                   Log::info(['Secondary >>', $secondary->id ,$row]);
                   $tertiary->name =  $row['name'];
                   $tertiary->description =  $row['description'];
                   $tertiary->save();
                   return Tertiary::find($tertiary->id);
               }else{
                   Log::info([$secondary->skill_id, $secondary->id]);
                    $row['skill_id'] = $secondary->skill_id;
                    $row['skill_secondary_id'] = $secondary->id;
                    unset($row['secondary']);
                    unset($row['primary']);
                    unset($row['id']);
                    Log::info('Create Tertiary',$row);
                   return Tertiary::firstOrCreate($row);
               }
            }else{
                return Tertiary::find(1);
            }
        }else{
            $skill = Skill::where('name',$row['name'])->first();
            if($skill){
                $skill->name =  $row['name'];
                $skill->description =  $row['description'];
                $skill->save();
                return Skill::find($skill->id);
            }else{
                return Skill::firstOrCreate($row);
            }
        }
    }
}
