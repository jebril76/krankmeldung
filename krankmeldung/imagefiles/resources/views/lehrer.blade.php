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
            @csrf
            <span id="DetailList">
                <table width="100%" class="mt-3">
                    <tr>
                            <td nowrap width="1%" style="padding-left:16px">
                            <h5>
                                Meldungen für den <input id="datepicker" name="setdate" class="btn btn-light btn-outline-secondary btn-sm mb-2" size="10" type="text" value="<?php echo $setdate; ?>">
                            </h5>
                        </td>
                    </tr>
                </table>
                <table class ="card" width="100%" style="display: revert;">
                    <tr width="100%" valign="top">
                        <td valign="top" align="left" class="card-body">
                            <span id="ReportList">
                            </span>
                        </td>
                    </tr>
                </table>
            </span>
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
                $('[id^="del-"]').hide();
            }
        });
        $("#datepicker").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
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
                        $('[id^="del-"]').hide();
                    }
                });
            }
        });
        $('#ReportList').on("click", "tr[rel^='tab-']", function(){
            var id=$(this).attr("rel").split("-");
            var now=new Date();
            var stayear=now.getFullYear();
            if (now.getMonth()<8) stayear=stayear-1
            let stadate="01.08."+stayear;
            $.ajax({
                url:"{{ route('l_show_rep') }}",
                type:"POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data:{'sid':id[1],'stadate':stadate, 'enddate':''},
                success:function (data) {
                    $('#DetailList').html(data);
                    $("#stadate").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                    $("#enddate").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                }
            })
        });
        $('#DetailList').on("click", "#back", function(){
            window.location = "/l";
        });
        $('#DetailList').on("change", "[id$=date]", function(){
            var id=$("[id^='id-']").attr("id").split("-");
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
                let stda = $('#stadate').val().split('.');
                let enda = $('#enddate').val().split('.');
                let sda = new Date(stda[2], stda[1]-1, stda[0]);
                let eda = new Date(enda[2], enda[1]-1, enda[0]);
                if (sda>eda) {
                    alert("Das Von-Datum sollte vor dem Bis-Datum liegen!");
                    data =-1;
                    datumgut = false;
                }
            }
            if(datumgut) {
                $.ajax({
                    url:"{{ route('l_show_rep') }}",
                    type:"POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data:{'sid':id[1],'stadate':$("#stadate").val(), 'enddate':$("#enddate").val()},
                    success:function (data2) {
                        $('#DetailList').html(data2);
                        $("#enddate").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                        $("#stadate").datepicker({ showWeek: true, beforeShowDay: $.datepicker.noWeekends });
                    }
                });
            }
        });
    });
</script>
@endpush