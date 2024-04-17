@extends('layouts.libs')
<?php
if (date('N')>5) $setdate=date("d.m.Y", time()+(-86400*(date('N')-8)));
else $setdate=date("d.m.Y"); 
?>
@section('content')
    <div class="card m-2">
        <div id="ReportList" class="table-responsive">
        </div>
    </div>
@endsection
@push('script')
<script type="text/javascript">
    $(document).ready(function() {
        $.ajax({
            url:"{{ route('i_reported_sus') }}",
            type:"GET",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data:{'sdate':'<?php echo $setdate; ?>'},
            success:function (data2) {
                $('#ReportList').html(data2);
                $('[id^="del-"]').hide();
                $(".table-striped").removeClass("table-striped");
            }
        });
    });
    var $el = $(".table-responsive");
    var striped=true;
    function anim() {
        var rowh= ($('tr').height());
        var rowa= ($('tr:eq(1)').height());
        var rowb= ($('tr:eq(2)').height());
        if (rowh == undefined) rowh=47;
        else {
            if($(".table-responsive").height()<($(".table-responsive tr").length*rowh)){
                var row1 = $('tr:first').remove().clone();
                $('.table').append(row1);
                if (striped) {
                    $(".table>tbody>tr:nth-child(odd)>td").css({backgroundColor: "#f2f2f2"});
                    $(".table>tbody>tr:nth-child(even)>td").css({backgroundColor: "#ffffff"});
                    striped=false;
                }
                else {
                    $(".table>tbody>tr:nth-child(even)>td").css({backgroundColor: "#f2f2f2"});
                    $(".table>tbody>tr:nth-child(odd)>td").css({backgroundColor: "#ffffff"});
                    striped=true;
                }
            }
            else {
                $(".table").addClass("table-striped");
            }
        }
        $el.animate({scrollTop: rowa+rowb}, {duration:{{ config('custom.infoscreenspeed') }}, easing:'linear', complete: anim});
    }
    anim();
</script>
<style type="text/css">
    body {
        overflow: hidden;
    }
    .table-responsive{
        height: calc(-24px + 100vh); width:100%;
        overflow-y: auto;
        border:2px solid #444;
        overflow: hidden;
    }
<style>
@endpush
