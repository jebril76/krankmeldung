<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\report;
use App\Models\student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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

function getBackups()
{
    $dir = storage_path('backups');
    return $files = collect(\File::allFiles($dir))
        ->filter(function ($file) {
            return in_array($file->getExtension(), ['sql']);
        })
        ->sortByDesc(function ($file) {
            return $file->getCTime();
        })->values()
        ->map(function ($file) {
            return [$file->getBaseName(), $file->getCTime()];
        });
}

class ReportController extends Controller
{
    public function v_config()
    {
        $files=getBackups();
        return view('config')->with(compact('files'));
    }

    public function m_reported_sus(Request $request)
    {
        $sdate = convertDate($request->post('sdate'));
        $now = date('Y-m-d');
        $data=DB::select('
            WITH CTE AS (
                SELECT r.student_id, r.comment, r.date, r.date - INTERVAL (ROW_NUMBER() OVER (PARTITION BY r.student_id ORDER BY r.date)) DAY AS conday
                FROM reports r
                WHERE r.date >= ?)
            SELECT s.id, s.class, s.firstname, s.lastname, r.comment, r.date AS ED
            FROM (
                SELECT student_id, MIN(date) AS SD
                FROM CTE
                GROUP BY student_id, conday) AS CTE_SD
                INNER JOIN students s ON CTE_SD.student_id = s.id
                INNER JOIN reports r ON CTE_SD.student_id = r.student_id AND CTE_SD.SD = r.date
                WHERE CTE_SD.SD = ?
                ORDER BY s.class, s.lastname, s.firstname;
            ', [$sdate, $sdate]);
        $output = '';
        if (count($data) > 0) {
            $output = '<table width="100%" class="table table-striped">';
            foreach ($data as $row) {
                $output .= '
                    <tr width="100%" rel="tab-' . $row->id . '">
                        <td>
                            <input id="del-' . $row->id . '" type="button" class="btn btn-light btn-outline-secondary btn-sm" value="<=">
                        </td>
                        <td nowrap width="1%" valign="middle">
                            ' . $row->firstname . ' ' . $row->lastname .' (' . $row->class . ')
                        </td>
                        <td width="100%">
                            <input id="com-' . $row->id . '" width="100%" style="width:100%" size="" type="text" value="' . $row->comment . '">
                        </td>';
                if (date($sdate)>=$now) $output .= '
                        <td>
                            <input id="enddate-' . $row->id . '" name="enddate-' . $row->id . '" class="btn btn-light btn-outline-secondary btn-sm" size="10" type="text" value="'.convertDate($row->ED).'">
                        </td>';
                $output .= '
                    </tr>';
            }
            $output .= '</table>';
        } else {
            $output .= '<table><tr><td>' . 'Kein Meldungen' . '</td></tr></table>';
        }
        return $output;
    }

    public function i_reported_sus(Request $request)
    {
        $sdate = convertDate($request->get('sdate'));
        $now = date('Y-m-d');
        $data=DB::select('
            WITH CTE AS (
                SELECT r.student_id, r.comment, r.date, r.date - INTERVAL (ROW_NUMBER() OVER (PARTITION BY r.student_id ORDER BY r.date)) DAY AS conday
                FROM reports r
                WHERE r.date >= ?)
            SELECT s.id, s.class, s.firstname, s.lastname, r.comment, r.date AS ED
            FROM (
                SELECT student_id, MIN(date) AS SD
                FROM CTE
                GROUP BY student_id, conday) AS CTE_SD
                INNER JOIN students s ON CTE_SD.student_id = s.id
                INNER JOIN reports r ON CTE_SD.student_id = r.student_id AND CTE_SD.SD = r.date
                WHERE CTE_SD.SD = ?
                ORDER BY s.class, s.lastname, s.firstname;
            ', [$sdate, $sdate]);
        $output = '';
        if (count($data) > 0) {
            $output = '<table width="100%" class="table">';
            foreach ($data as $row) {
                $output .= '
                    <tr width="100%" rel="tab-' . $row->id . '">
                        <td>' . $row->firstname . ' ' . $row->lastname .' (' . $row->class . ') ';
                if ($row->comment != '') $output .= '['.$row->comment.'] ';
                if (date($row->ED) > $now) $output .= '('.convertDate($row->ED).')';
                $output .= '
                    </tr>';
            }
            $output .= '</table>';
        } else {
            $output .= '<table><tr><td>' . 'Kein Meldungen' . '</td></tr></table>';
        }
        return $output;
    }

    public function m_create_rep(Request $request)
    {
        $sdate = convertDate($request->post('sdate'));
        $sid = $request->post('sid');
        $repstore = new report();
        $repstore->date=$sdate;
        $repstore->student_id=$sid;
        $repstore->comment='';
        $repstore->save();
        return (new ReportController)->m_reported_sus($request);
    }

    public function m_edit_days(Request $request)
    {
        $edate = convertDate($request->post('edate'));
        $sdate = convertDate($request->post('sdate'));
        $sid = $request->post('sid');
        $aryRange = [];
        $iDateFrom = mktime(1, 0, 0, substr($sdate, 5, 2), substr($sdate, 8, 2), substr($sdate, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($edate, 5, 2), substr($edate, 8, 2), substr($edate, 0, 4));
        if ($iDateTo >= $iDateFrom) {
            while ($iDateFrom<$iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        foreach ($aryRange as $date) {
            DB::select('
                INSERT reports (date, student_id, comment)
                SELECT ?, ?, ""
                WHERE NOT EXISTS 
                    (SELECT date, student_id
                        FROM reports
                        WHERE date=? AND student_id=?);
                ', [$date, $sid, $date, $sid]);
        }
        report::where('date', ">", $edate)->where('student_id', $sid)->delete();
    }

    public function m_update_com(Request $request)
    {
        $sdate = convertDate($request->post('sdate'));
        $sid = $request->post('sid');
        $comment = $request->post('comment');
        report::where('student_id', $sid)
            ->where('date', $sdate)
            ->update(['comment' => $comment]);
    }

    public function m_destroy_rep(Request $request)
    {
        $sdate = date(convertDate($request->post('sdate')));
        $sid = $request->post('sid');
        if ($request->post('serie')) {
            $eq=">=";
        }
        else {
            $eq="=";
        }
//If Montag, dann lösche ab Samstag
        if (date('N', strtotime($sdate))==1) {
            $adate=date('Y-m-d', strtotime($sdate . ' - 2 day'));
            $repdelet = report::whereBetween('date', [$adate, $sdate])->where('student_id', $sid)->delete();
        }
        $repdelet = report::where('date', $eq, $sdate)->where('student_id', $sid)->delete();
        return (new ReportController)->m_reported_sus($request);
    }

    public function c_restore_rep(Request $request)
    {
        $file = $request->file('file');
        $filename = $request->filename;
        if ($request->file=='') {
            $file=storage_path('backups/'.$filename);
        }
        Artisan::call('app:restore', ['file' => $file]);
        return redirect('/m');
    }
    public function c_backup_rep(Request $request)
    {
        if ($request->backup2mail!=null) {
            Artisan::call('app:sendemail');
        }
        if ($request->backup2server!=null) {
            Artisan::call('db:backup');
        }
            return redirect('/m');
    }
    public function c_trash_rep(Request $request)
    {
        Artisan::call('db:backup');
        report::query()->delete();
        return redirect()->back()->with('message', $request->import.'Ein neues Backup wurde erstellt. Alle Krankmeldungen wurden gelöscht.');
    }
    public function c_trash_all(Request $request)
    {
        Artisan::call('db:backup');
        report::query()->delete();
        student::query()->delete();
        return redirect()->back()->with('message', $request->import.'Ein neues Backup wurde erstellt. Alle Daten wurden gelöscht.');
    }

    public function l_show_rep(Request $request)
    {
        $sdate = convertDate($request->post('stadate'));
        $sid = $request->post('sid');
        if ($request->post('enddate')=='') {
            $data2=report::where('student_id', $sid)
                ->where('date', '>=', $sdate)
                ->get();
            $edate='';
        }
        else {
            $data2=report::where('student_id', $sid)
                ->whereBetween('date', [$sdate, convertDate($request->post('enddate'))])
                ->get();
            $edate = $request->post('enddate');
        }
        $data=student::where('id', $sid)
            ->get();
        $output = '
            <table width="100%" class="mt-3">
                <tr>
                    <td nowrap width="1%" style="padding-left:16px">
                        <h5>
                            <input type="button" id="back" value="<-">
                            Meldungen für ' . $data[0]->firstname . ' ' . $data[0]->lastname . ' (' . $data[0]->class .')
                            <input type="button" id="print" value="Drucken" onClick="window.print()">
                            <div id="id-'.$data[0]->id.'">
                            </div>
                        </h5>
                    </td>
                </tr>
            </table>
            <form action="{{ route("index") }}" method="post">
            <table width="100%" style="display: revert;" class="table table-striped mb-0">
                <tr width="100%" valign="top" class="table table-striped">
                    <td valign="top" colspan="2" align="left" class="pt-3" nowrap>
                        Von: <input id="stadate" name="stadate" class="btn btn-light btn-outline-secondary btn-sm mb-2" size="10" type="text" value="'. convertDate($sdate) .'">
                    </td>
                </tr>';
        if (count($data2) > 0) {
            foreach ($data2 as $row) {
                if(date('N', strtotime($row->date))<6) {
                    $output .= '
                        <tr width="100%">
                            <td nowrap width="1%">
                                ' . ConvertDate($row->date) . '
                            </td>
                            <td width="100%">
                                ' . $row->comment . '
                            </td>
                        </tr>';
                }
            }
        } else {
            $output .= '<tr><td colspan="2" >' . 'Kein Meldungen' . '</td></tr>';
        }
        $output .= '
            <tr width="100%" valign="top" >
                <td valign="top" colspan="2" align="left" class="pt-3" nowrap>
                    Bis: <input id="enddate" name="enddate" class="btn btn-light btn-outline-secondary btn-sm mb-2" size="10" type="text" value="'. $edate .'">
                </td>
            </tr>
        </table>
        <table width="100%" class="card-footer table-striped">
            <tr>
                <td colspan="2" style="padding-left:16px" class="pt-3">
                    <h5>
                        <input type="button" id="back" value="<-">
                    </h5>
                </td>
            </tr>
        </table>
        </form>';
        return $output;
    }
}
