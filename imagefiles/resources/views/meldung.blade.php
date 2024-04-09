@extends('layouts.libs')
<?php
if (date('N')>5) $setdate=date("d.m.Y", time()+(-86400*(date('N')-8)));
else $setdate=date("d.m.Y"); 
?>
@section('content')
    <div width="100%" class="card m-2">
        <div class="card-header" align="center">
            <h4>
                Krankmeldungen
            </h4>
        </div>
        <form action="{{ route('m_search_sus') }}" method="post">
            <table width="100%" class="mt-3">
                <tr>
                    <td nowrap width="1%" style="padding-left:16px">
                        <h5>
                            Meldungen für den
                            <input id="datepicker" name="setdate" class="btn btn-light btn-outline-secondary btn-sm mb-2" size="10" type="text" value="<?php echo $setdate; ?>">
                        </h5>
                    </td>
                    <td align="left" valign="top" nowrap style="padding-right:16px">
                        <input type="text" class="" size="" width="100%" style="width:100%" placeholder="Vorname Nachname Klasse Rufnummer" name="q" id="searchStudent" title="Mehrere Begriffe durch Leerzeichen trennen. Rufnummern werden ab 3 Ziffern berücksichtigt. Return meldet den obesten Schüler.">
                    </td>
                </tr>
            </table>
            <table class ="card" width="100%" style="display: revert;">
                <tr width="100%" valign="top">
                    <td width="20%" align="right">
                        <table class="card" style="display: revert;">
                            <tr>
                                <th width="1%" class="card-header" nowrap>
                                    <center>
                                        Alle Schüler
                                    </center>
                                </th>
                            </tr>
                            <tr>
                                <td valign="top" align="right" class="card-body">
                                    <span id="StudentList">
                                        </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="80%">
                        <table class="card" width="100%" style="display: revert;">
                            <tr width="100%">
                                <th width="100%" class="card-header" nowrap>
                                    <center>
                                        Fehlende Schüler
                                    </center>
                                </th>
                            </tr>
                            <tr>
                                <td valign="top" align="left" class="card-body">
                                    <span id="ReportList">
                                        </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <a href="/c">
                Einstellungen
            </a>
            <br>
            <a href="/i">
                Infoscreen
            </a>
            <br>
            <a href="/l">
                Ansicht für Lehrer
            </a>
        </form>
    </div>
@endsection
@push('script')
<script type="text/javascript">
    $(document).ready(function() {
        $.ajax({
            url:"{{ route('m_reported_sus') }}",
            type:"POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data:{'sdate':$("#datepicker").val()},
            success:function (data2) {
                $('#ReportList').html(data2);
                $("[id^=\'enddate-\']").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
            }
        });
        $.ajax({
            url:"{{ route('m_search_sus') }}",
            type:'POST',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data:{'query':"",'sdate':$("#datepicker").val()},
            success:function (data2) {
                $('#StudentList').html(data2);
            }
        })
        $("#datepicker").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
        $("#searchStudent").focus();
        $("#datepicker").change(function () {
            $("#ui-datepicker-div").hide();
            var datumgut = true;
            var data=$(this).val();
            try{
                jQuery.datepicker.parseDate("dd.mm.yy", data, null); 
            }
            catch(error){
                alert("Datum konnte nicht erkannt werden\n"+error);
                $(this).val(null);
                data =-1;
                datumgut = false;
            }
            if(datumgut) {
                $.ajax({
                    url:"{{ route('m_reported_sus') }}",
                    type:"POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data:{'sdate':data},
                    success:function (data2) {
                        $('#ReportList').html(data2);
                        $("[id^=\'enddate-\']").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                    }
                });
                $.ajax({
                    url:"{{ route('m_search_sus') }}",
                    type:"POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data:{'query':"",'sdate':$("#datepicker").val()},
                    success:function (data2) {
                        $('#StudentList').html(data2);
                    }
                })
            }
            $('#searchStudent').focus();
        });
    });
    $('#ReportList').on("change", "[id^='enddate-']", function(){
        $("#ui-datepicker-div").hide();
        var id=$(this).attr("id").split("-");
        var datumgut = true;
        var data=$(this).val();
        try{
            jQuery.datepicker.parseDate("dd.mm.yy", data, null); 
        }
        catch(error){
            alert("Datum konnte nicht erkannt werden\n"+error);
            $(this).val(null);
            data =-1;
            datumgut = false;
        }
        if(datumgut) {
            let toda = $('#datepicker').val().split('.');
            let krda = $(this).val().split('.');
            let tda = new Date(toda[2], toda[1]-1, toda[0]);
            let kda = new Date(krda[2], krda[1]-1, krda[0]);
            if (kda<=tda) {
                alert ('Das Datum muss nach dem '+$('#datepicker').val()+' liegen!');
                data =-1;
                datumgut = false;
            }
        }
        if(datumgut) {
            $.ajax({
                url:"{{ route('m_edit_days') }}",
                type:"POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data:{'sid':id[1], 'sdate':$("#datepicker").val(), 'edate':data},
            })
        }
        $('#searchStudent').focus();
    });
    $('#StudentList').on("click", "[id^='add-']", function(){
        var heute = new Date();
        var heute = new Date(heute.getFullYear(), heute.getMonth(), heute.getDate());
        var dateParts = $('#datepicker').val().split(".");
        var datum = new Date(+dateParts[2], dateParts[1]-1, +dateParts[0]);
        if (datum<heute) {
            var ok=false;
            if (confirm("Das Datum liegt in der Vergangen. Dennoch fortfahren?")) ok=true;
        }
        else var ok=true;
        if (ok) {
            var id=$(this).attr("id").split("-");
            $.ajax({
                url:"{{ route('m_create_rep') }}",
                type:"POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data:{'sid':id[1],'sdate':$("#datepicker").val()},
                success:function (data) {
                    $('#ReportList').html(data);
                    $("[id^=\'enddate-\']").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                    $('#searchStudent').val('');
                    $('#com-'+id[1]).focus();
                    $.ajax({
                        url:"{{ route('m_search_sus') }}",
                        type:"POST",
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        data:{'query':"",'sdate':$("#datepicker").val()},
                        success:function (data) {
                            $('#StudentList').html(data);
                        }
                    })
                }
            })
        }
    });
    $('#searchStudent').on('keydown',function search(e) {
        if(e.keyCode == 13) {
            var ok=false;
            if ($('#searchStudent').val().length>2) {
                var id=$("[id^='add-']").first().attr('id').split("-");
                var heute = new Date();
                var heute = new Date(heute.getFullYear(), heute.getMonth(), heute.getDate());
                var dateParts = $('#datepicker').val().split(".");
                var datum = new Date(+dateParts[2], dateParts[1]-1, +dateParts[0]);
                if (datum<heute) {
                    if (confirm("Das Datum liegt in der Vergangen. Dennoch fortfahren?")) ok=true;
                }
                else var ok=true;
            }
            if (ok) {
                $.ajax({
                    url:"{{ route('m_create_rep') }}",
                    type:"POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data:{'sid':id[1],'sdate':$("#datepicker").val()},
                    success:function (data) {
                        $('#ReportList').html(data);
                        $("[id^=\'enddate-\']").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                        $('#searchStudent').val('');
                        $('#com-'+id[1]).focus();
                        $.ajax({
                            url:"{{ route('m_search_sus') }}",
                            type:"POST",
                            data:{'query':"",'sdate':$("#datepicker").val()},
                            success:function (data) {
                                $('#StudentList').html(data);
                            }
                        })
                    }
                })
            }
        }
    });
    $('#ReportList').on("change", "[id^='com-']", function(){
        var id=$(this).attr("id").split("-");
        $.ajax({
            url:"{{ route('m_update_com') }}",
            type:"POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data:{'sid':id[1],'sdate':$("#datepicker").val(),'comment':$(this).val()}
        })
    });
    $('#ReportList').on('keydown', "[id^='com-']", function search (e){
        if(e.keyCode == 13) {
            $('#searchStudent').focus();
        }
    });
    $('#ReportList').on("click", "[id^='del-']", function(){
        var id=$(this).attr("id").split("-");
        var heute = new Date();
        var heute = new Date(heute.getFullYear(), heute.getMonth(), heute.getDate());
        var dateParts = $('#datepicker').val().split(".");
        var datum = new Date(+dateParts[2], dateParts[1]-1, +dateParts[0]);
        var serie=0;
        if (datum>=heute) {
            var edateParts = $('#enddate-'+id[1]).val().split(".");
            var edatum = new Date(+edateParts[2], edateParts[1]-1, +edateParts[0]);
            if (edatum>datum) { 
                if (confirm("Sollen alle Tage weiteren Tage gelöscht werden?")) {
                    var serie=1;
                }
            }
        }
        $.ajax({
            url:"{{ route('m_destroy_rep') }}",
            type:"POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data:{'sid':id[1],'sdate':$("#datepicker").val(),'serie':serie},
            success:function (data) {
                $('#ReportList').html(data);
                $("[id^=\'enddate-\']").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                $.ajax({
                    url:"{{ route('m_search_sus') }}",
                    type:"POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data:{'query':"",'sdate':$("#datepicker").val()},
                    success:function (data) {
                        $('#StudentList').html(data);
                    }
                })
            }
        })
        $('#searchStudent').focus();
    });
    $('#searchStudent').on('keyup',function() {
        var query = $(this).val(); 
        var sdate = $("#datepicker").val();
        if ($('#searchStudent').val().length>2) {
            $.ajax({
                url:"{{ route('m_search_sus') }}",
                type:"POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data:{'query':query,'sdate':sdate},
                success:function (data) {
                    $('#StudentList').html(data);
                }
            })
        }
    });
</script>
@endpush
