<?php

namespace App\Http\Controllers;

use App\Imports\CsvImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class HomeownerController extends Controller
{

    private $title_list = [
        'Mister',
        'Mrs',
        'Mr',
        'Dr',
        'Ms',
        'Prof'
    ];

    public function cleanCsv(Request $request){

        $validated = $request->validate([
            'file' => 'required|file'
        ]);

        $homeowner_data = Excel::toArray(new CsvImport, $validated['file'])[0];

        // Remove header
        array_shift($homeowner_data);

        $homeowners_cleaned = [];

        foreach($homeowner_data as $entry){
            $entry = $entry[0];

            $name_dot_stripped = str_replace('.', '', $entry);
            $parts = explode(' ', $name_dot_stripped);
            $people = [];
            
            // Two people
            if(in_array('&', $parts) || in_array('and', $parts)){

                $and_pos = array_search('&', $parts) !== false ? array_search('&', $parts) : array_search('and', $parts);
                
                $partner_1 = [];
                for ($i=$and_pos + 1; $i < count($parts); $i++) { 
                    $partner_1[] = $parts[$i];   
                }
                $people[] = $partner_1;


                $partner_2 = [];
                for ($i=0; $i < $and_pos; $i++) { 
                    $partner_2[] = $parts[$i];   
                }

                $people[] = $partner_2;


            }else{ //One person

                $people[] = $parts;

            }
            
            

            foreach($people as $p_key => $person){

                $title = null;
                $first_name = null;
                $initial = null;
                $last_name = null;

                foreach($person as $name_part){
                    if(in_array($name_part, $this->title_list)){
                        $title = $name_part;
                    }else if(strlen($name_part) == 1){
                        $initial = $name_part;
                    }else if($last_name == null){
                        $last_name = $name_part;
                    }else if($last_name != null){
                        $first_name = $last_name;
                        $last_name = $name_part;
                    }
                }
                

                // Build partner 2 name
                if(count($people) > 1 && $p_key == 1){
                    $prev_person = $homeowners_cleaned[count($homeowners_cleaned)-1];

                    if($last_name == null){
                        // Use partner 1 last name
                        $last_name = $prev_person['last_name'];
                    }
                }

                $homeowners_cleaned[] = compact(
                    'title',
                    'first_name',
                    'initial',
                    'last_name'
                );

            }

        }


        return response()->json($homeowners_cleaned);


    }

    
}
