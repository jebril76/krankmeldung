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
        $sdate = convertDate($request->post('sdate'));
        $search = preg_split('/\s+/', $request->post('query'), -1, PREG_SPLIT_NO_EMPTY);
/*
        $names = array();
        $numbers = array();
        $mixed = array();
        foreach($search as $part){
            if(intval($part)){
                if (intval($part)<11) $mixed[]=$part;
                if (strlen($part)>2) $numbers[] = $part;
            }else{
                if (preg_match('~[0-9]+~', $part)) {
                    $mixed[]=$part;
                } else {
                    if (strlen($part)>2) $names[] = $part;
                }
            }
        }
        $data=student::where(function($q) use($names,$mixed,$numbers){
            foreach ($names as $name) {
                foreach ($numbers as $number) {
                    foreach ($mixed as $mixe) {
                        $q->where(function($and) use($name, $number, $mixe) {
                            $and->where('firstname', 'LIKE', '%'.$name.'%')
                                ->orwhere('lastname','LIKE', '%'.$name.'%')
                                ->orwhere('class','LIKE', '%'.$mixe.'%')
                                ->orwhere('phones','LIKE', '%'.$number.'%');
                        });
                    }
                }
            }
        })
//Schüler die bereits ausgewählt wurden raus nehmen.
            ->whereNotExists(function($notin) use($sdate){
                $notin->select(DB::raw(1))
                    ->from('reports')
                    ->where('reports.date', $sdate)
                    ->whereRaw('students.id = reports.student_id');
            })
//Nach Klasse und Nachname sortieren und auf 50 begrenzen.
            ->orderBy('class')
            ->orderBy('lastname')
            ->limit(50)
            ->get();
*/        
//Schüler mit passendem Vor-/Nachnamen oder Klasse wählen.
        $data=student::where(function($q) use($search){
            foreach ($search as $part) {
                if (strlen($part)>2) $phopart=$part;
                else $phopart="xxx";
                $q->where(function($and) use($part, $phopart) {
                        $and->where('firstname', 'LIKE', '%'.$part.'%')
                            ->orwhere('lastname','LIKE', '%'.$part.'%')
                            ->orwhere('class','LIKE', '%'.$part.'%')
                            ->orwhere('phones','LIKE', '%'.$phopart.'%');
                    });
                }
            })
//Schüler die bereits ausgewählt wurden raus nehmen.
            ->whereNotExists(function($notin) use($sdate){
                $notin->select(DB::raw(1))
                    ->from('reports')
                    ->where('reports.date', $sdate)
                    ->whereRaw('students.id = reports.student_id');
            })
//Nach Klasse und Nachname sortieren und auf 50 begrenzen.
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
