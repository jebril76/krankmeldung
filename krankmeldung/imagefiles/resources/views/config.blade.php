@extends('layouts.libs')
@section('content')
    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif
    <div width="100%" class="card m-2">
        <table width="100%" id="mytable">
            <tr>
                <td align="center" nowrap class="card-header" colspan="6">
                    <h4>
                        Krankmeldungen
                    </h4>
                </td>
            </tr>
            <tr>
                <td width="33%" class="p-2" align="center" nowrap colspan="2">
                    <button id="import" class="btn btn-light btn-outline-secondary btn-sm">
                        Daten importieren aus ...
                    </button>
                </td>
                <td width="34%" class="p-2" align="center" nowrap colspan="2">
                    <button id="export" class="btn btn-light btn-outline-secondary btn-sm">
                        Krankmeldungen exportieren und als ...
                    </button>
                </td>
                <td width="33%" class="p-2" align="center" nowrap colspan="2">
                    <button id="delete" class="btn btn-light btn-outline-secondary btn-sm">
                        Löschen ...
                    </button>
                </td>
            </tr>
            <tr id="i_" style="display:none">
                <td class="p-2" align="center" nowrap colspan="3">
                    <button id="schueler" class="btn btn-light btn-outline-secondary btn-sm">
                        Schülerdaten aus Schild-NRW importieren.
                    </button>
                </td>
                <td class="p-2" align="center" nowrap colspan="3">
                    <button id="krank" class="btn btn-light btn-outline-secondary btn-sm">
                        Krankmeldungen aus Backup importieren.
                    </button>
                </td>
            </tr>
            <tr id="i_s_" style="display:none">
                <td class="p-2" colspan="6">
                    <form method="post" action="{{ route('c_import_sus1')}}" enctype="multipart/form-data">
                        @csrf
                        Fahre mit der Maus über die Bilder um sie zu vergrößern. Exportiere die Daten aus Schild-NRW.<br>
                        <img src="{{url('/img/shot1.JPG') }}" width="100px">
                        <br>
                        <img src="{{url('/img/shot2.JPG') }}" width="100px">
                        <br>
                        Klicke unten auf "Durchsuchen..." und wähle auf dem Desktop die Datei <b>SchuelerBasisdaten.csv</b> aus. Klicke auf Importieren. Anschließend kannst du die Datei vom Desktop löschen.<br><br>
                        <input type="file" accept=".csv" name="file" style="background-color:transparent; border-style:none;"> 
                        <input type="submit" value="Importieren" name="Import" class="btn btn-light btn-outline-secondary btn-sm"><br>
                        Danach können optional weitere Telefondaten importiert werden.
                    </form>
                </td>
            </tr>
            <tr id="i_s_2_" style="display:none">
                <td class="p-2" colspan="6">
                    <form method="post" action="{{ route('c_import_sus2')}}" enctype="multipart/form-data">
                        @csrf
                        Optional können weitere Telefonnummern aus Schild importiert werden.<br>
                        <img src="{{url('/img/shot3.JPG') }}" width="100px">
                        <br>
                        Wähle dieses Mal die Datei <b>SchuelerTelefonnummern.csv</b> aus. Klicke auf Importieren. Anschließend kannst du die Datei vom Desktop löschen.<br><br>
                        <input type="file" accept=".csv" name="file" style="background-color:transparent; border-style:none;"> 
                        <input type="submit" value="Importieren" name="Import" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
            </tr>
            <tr id="i_k_" style="display:none">
                <td class="p-2" align="center" nowrap colspan="3">
                    <button id="emailin" class="btn btn-light btn-outline-secondary btn-sm">
                        Krankmeldungen aus E-Mail-Datei hochladen.
                    </button>
                </td>
                <td class="p-2" align="center" nowrap colspan="3">
                    <button id="server" class="btn btn-light btn-outline-secondary btn-sm">
                        Krankmeldungen aus einer Liste von Backups auswählen.
                    </button>
                </td>
            </tr>
            <tr id="i_k_e_" style="display:none">
                <td class="p-2" colspan="6">
                    <form method="post" action="{{ route('c_restore_rep')}}" enctype="multipart/form-data">
                        @csrf
                        Im Postfach <b>{{config('custom.backupmail')}}</b> findest du E-Mails mit täglichen Backups. Öffne die gewünschte Email und speichere die Backupdatei (<b>backup-....sql</b>) auf deinem Desktop. Klicke auf "Durchsuchen..." und wähle die Datei auf dem Desktop aus. Klicke auf Importieren. Anschließend kannst du die Datei vom Desktop löschen.<br><br>
                        <input type="file" accept=".sql" name="file" class="btn btn-light btn-outline-secondary btn-sm" >
                        <input type="submit" value="Importieren" id="import" name="import" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
            </tr>
            <tr id="i_k_s_" style="display:none">
                <td class="p-2" colspan="6">
                    <form method="post" action="{{ route('c_restore_rep')}}" enctype="multipart/form-data">
                        @csrf
                        Wähle das gewünschte Backup und klicke auf Importieren.<br>
                        <select name="filename" id="filename" class="btn btn-light btn-outline-secondary btn-sm">
                            @foreach ($files as $file)
                                <option value="{{ $file['0'] }}">{{ $file['0'] }}</option>
                            @endforeach
                        </select>
                        <input type="submit" value="Importieren" id="import" name="import" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
            </tr>
            <tr id="e_" style="display:none">
                <td class="p-2" align="center" nowrap colspan="3">
                    <form method="post" action="{{ route('c_backup_rep')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="submit" value="Krankmeldungen an {{config('custom.backupmail')}} verschicken" name="backup2mail" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
                <td class="p-2" align="center" nowrap colspan="3">
                    <form method="post" action="{{ route('c_backup_rep')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="submit" value="Krankmeldungen als Datei auf diesem Gerät ablegen" name="backup2server" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
            </tr>
            <tr id="d_" style="display:none">
                <td class="p-2" align="center" nowrap colspan="2">
                    <form method="post" action="{{ route('c_trash_sus')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="submit" value="Alle Schülerdaten löschen" name="backup2server" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
                <td class="p-2" align="center" nowrap colspan="2">
                    <form method="post" action="{{ route('c_trash_rep')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="submit" value="Alle Krankmeldungen löschen" name="backup2server" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
                <td class="p-2" colspan="2">
                    <form method="post" action="{{ route('c_trash_all')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="submit" value="Alle Schülerdaten und Krankmeldungen löschen" name="backup2server" class="btn btn-light btn-outline-secondary btn-sm">
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <button id="back" class="btn btn-light btn-outline-secondary btn-sm">
                        <-
                    </button>
                </td>
            </tr>
        </table>
    </div>
@endsection
@push('script')
<script type="text/javascript">
    function striped() {
        var table = document.getElementById("mytable");
        var k = 0;
        for (var j = 0, row; row = table.rows[j]; j++) {
            if (!(row.style.display === "none")) {
                if (k % 2) {
                    row.style.backgroundColor = "#FFFFFF";
                } else {
                    row.style.backgroundColor = "#F2F2F2";
                }
                k++;
            }
        }
    };
    striped();
    if ("{{ session()->get('message') }}"=="SchülerInnen wurden angelegt.") {
        $("#i_").show();
        $("#i_s_2_").show();
        striped();
    }
    $(document).ready(function() {
        $('#back').on("click", function(){
            window.location = "/m";
        });
        $("img").on('mouseover', function () {
            var wid=$(this).width();
            $(this).width($(this).parent().width()*0.7);
        });
        $("img").on('mouseleave', function () {
            $(this).width(100);
        });
        $("#import").on('click', function () {
            $("#i_").show();
            $("[id^='i_s_']").hide();
            $("[id^='i_k_']").hide();
            $("[id^='e_']").hide();
            $("[id^='d_']").hide();
            striped();
        });
        $("#schueler").on('click', function () {
            $("#i_s_").show();
            $("[id^='i_s_2_']").hide();
            $("[id^='i_k_']").hide();
            $("[id^='e_']").hide();
            $("[id^='d_']").hide();
            striped();
        });
        $("#krank").on('click', function () {
            $("#i_k_").show();
            $("[id^='i_s_']").hide();
            $("[id^='e_']").hide();
            $("[id^='d_']").hide();
            striped();
        });
        $("#emailin").on('click', function () {
            $("#i_k_e_").show();
            $("[id^='i_k_s_']").hide();
            $("[id^='i_s_']").hide();
            $("[id^='e_']").hide();
            $("[id^='d_']").hide();
            striped();
        });
        $("#server").on('click', function () {
            $("#i_k_s_").show();
            $("[id^='i_k_e_']").hide();
            $("[id^='i_s_']").hide();
            $("[id^='e_']").hide();
            $("[id^='d_']").hide();
            striped();
        });
        $("#export").on('click', function () {
            $("#e_").show();
            $("[id^='i_']").hide();
            $("[id^='d_']").hide();
            striped();
        });
        $("#delete").on('click', function () {
            $("#d_").show();
            $("[id^='i_']").hide();
            $("[id^='e_']").hide();
            striped();
        });
    });
</script>
@endpush