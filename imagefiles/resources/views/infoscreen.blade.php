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
        var $table;
        var $tbody;
        var $tableResponsive;
        var $el;
        var $rows;
        var $rowsto;
        var totalHeight = 0;
        var striped = true;
        var baseScrollSpeed = {{ config('custom.infoscreenspeed') }};
        function appendAndStyleRow() {
            if (striped) {
                $(".table>tbody>tr:nth-child(odd)>td").css("backgroundColor", "#f2f2f2");
                $(".table>tbody>tr:nth-child(even)>td").css("backgroundColor", "#ffffff");
                striped = false;
            } else {
                $(".table>tbody>tr:nth-child(even)>td").css("backgroundColor", "#f2f2f2");
                $(".table>tbody>tr:nth-child(odd)>td").css("backgroundColor", "#ffffff");
                striped = true;
            }
        }
        $.ajax({
            url: "{{ route('i_reported_sus') }}",
            type: "GET",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { 'sdate': '<?php echo $setdate; ?>' },
            success: function(data2) {
                $('#ReportList').html(data2);
                $table = $(".table");
                $tbody = $table.find("tbody");
                $tableResponsive = $(".table-responsive");
                $el = $tableResponsive;
                $rows = $tbody.find("tr");
                $rows.each(function() {
                    totalHeight += $(this).height();
                });
                if ($tableResponsive.height() < totalHeight) {
                    $table.append($rows.eq(0).clone());
                }
                anim();
            }
        });
        function anim() {
            var averageRowHeight = totalHeight / $rows.length;
            if ($tableResponsive.height() < totalHeight) {
                $rows.eq(0).remove();
                $table.append($rows.eq(1).clone());
                $tbody = $table.find("tbody");
                $rows = $tbody.find("tr");
                appendAndStyleRow();
            } else {
                $table.addClass("table-striped");
            }
    
            var actualRowHeight = $rows.eq(0).height();
            var speedAdjustmentFactor = actualRowHeight / averageRowHeight ;
            var adjustedScrollSpeed = baseScrollSpeed * speedAdjustmentFactor;
            $el.animate({ scrollTop: '+=' + actualRowHeight }, {
                duration: adjustedScrollSpeed,
                easing: 'linear',
                complete: anim
            });
        }
    });
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
