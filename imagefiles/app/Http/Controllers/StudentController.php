<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\report;
use App\Models\student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

function convertDate($SqlDate)
{
    $SqlTime=NULL;
    if ($SqlDate=="") return;
    if ($SqlDate=="0000-00-00") return;
    if (strlen($SqlDate)==19) {
        list($SqlDate, $SqlTime)=explode(" ", $SqlDate);
        $SqlTime = " ".$SqlTime;
    }

    if (substr_count($SqlDate,"-")>0) 
    {
        $date_array=explode("-",$SqlDate);
        return($date_array[2].".".$date_array[1].".".$date_array[0]).$SqlTime;
    }
    else {
        $date_array=explode(".",$SqlDate); 
        return($date_array[2]."-".$date_array[1]."-".$date_array[0]).$SqlTime;
    }
}

class StudentController extends Controller
{
    public function m_search_sus(Request $request)
    {
        function DigitsAndLetters($str) {
            return preg_match('/^\d{1,2}[a-zA-Z]$/', $str) === 1;
        }
        $sdate = convertDate($request->post('sdate'));
        $search = preg_split('/\s+/', $request->post('query'), -1, PREG_SPLIT_NO_EMPTY);
        $sdate = convertDate($request->post('sdate'));
        $search = preg_split('/\s+/', $request->post('query'), -1, PREG_SPLIT_NO_EMPTY);
        $data = Student::whereNotExists(function ($query) use ($sdate) {
                $query->select(DB::raw(1))
                    ->from('reports')
                    ->whereColumn('reports.student_id', 'students.id')
                    ->where('reports.date', $sdate);
            })
            ->where(function ($query) use ($search) {
                foreach ($search as $part) {
                    if ((ctype_alpha($part) || strpos($part, 'ä') !== false || strpos($part, 'ö') !== false || strpos($part, 'ü') !== false || strpos($part, 'ß') !== false) && strlen($part)>2) {
                        $query->where(function ($innerQuery) use ($part) {
                            $innerQuery->where('firstname', 'LIKE', '%' . $part . '%')
                                ->orWhere('lastname', 'LIKE', '%' . $part . '%');
                        });
                    } elseif (ctype_digit($part) && strlen($part)>2) {
                        $query->orWhere('phones', 'LIKE', '%' . $part . '%');
                    } elseif (DigitsAndLetters($part)){
                        $query->orWhere('class', 'LIKE', '%' . $part . '%');
                    }
                }
            })
            ->orderBy('class')
            ->orderBy('lastname')
            ->limit(50)
            ->get();
//Ausgabetabelle erstellen.
        $output = '';
        if (count($data) > 0) {
            $output = '<table class="table table-striped">';
            foreach ($data as $row) {
                $output .= '<tr nowrap><td nowrap>' . $row->firstname . ' ' . $row->lastname .' (' . $row->class . ')</td><td nowrap><input id="add-'.$row->id.'" type="button" class="btn btn-light btn-outline-secondary btn-sm" value="=>"></td></tr>';
            }
            $output .= '</table>';
        } else {
            $output .= '<table><tr><td nowrap>' . 'Kein Ergebnis' . '</td></tr></table>';
        }
        return $output;
    }

    public function c_import_sus1(Request $request)
    {
        $file = $request->file('file');
        $fileContents = file($file->getPathname());
        if (date("n")<8) $lastyear=date("Y")-1;
        else $lastyear=date("Y");
        report::query()
            ->where('date', '<', strval($lastyear-7).'-08-01')
            ->delete();
        student::query()->delete();
        foreach ($fileContents as $line) {
            $data = str_getcsv($line, ";");
//            $data[0]=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
            $phones=preg_replace('/[^0-9#]/', '', implode("#", array_filter([$data[4],$data[5],$data[6],$data[7]])));
            student::create([
                'firstname' => $data[0],
                'lastname' => $data[1],
                'class' => $data[2],
                'id' => $data[3],
                'phones' => $phones
            ]);
        }
        return redirect()->back()->with('message', 'SchülerInnen wurden angelegt.');
    }

    public function c_import_sus2(Request $request)
    {
        $csv = array_map('str_getcsv', file($request->file('file')));
        foreach ($csv as $line) {
            $data=str_getcsv($line[0],";");
//            $data[0]=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
            $q=student::Select('id')
                ->Where('firstname' , $data[0])
                ->Where('lastname', $data[1])
                ->Where('class', $data[2])
                ->get();
            $phone=preg_replace('/[^0-9]/', '', $data[3]);
            DB::update('UPDATE students SET phones = CONCAT(phones,' . '"#' . $phone . '") 
                WHERE id = ' . $q[0]->id . ' AND phones NOT LIKE "%' . $phone . '%"');
        }
        return redirect('/m');
    }

    public function c_trash_sus(Request $request)
    {
        student::query()->delete();
        return redirect()->back()->with('message', $request->import.'Ein neues Backup wurde erstellt. Alle SchülerInnen wurden gelöscht.');
    }
}
