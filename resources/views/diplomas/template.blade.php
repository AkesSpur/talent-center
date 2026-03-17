<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        /* 1. Load Arial Black */
        @font-face {
            font-family: 'Arial Black Custom';
            src: url('{{ $fontAriblk }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* 2. Load Calibri */
        @font-face {
            font-family: 'Calibri Custom';
            src: url('{{ $fontCalibri }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            width: 210mm;
            height: 297mm;
            font-family: 'DejaVu Serif', serif;
            background-color: #FAF8F5;
        }

        /* ── Main container ── */
        .diploma-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-color: #FAF8F5;
            overflow: hidden;
        }

        @if($backgroundPath)
            .diploma-bg {
                position: absolute;
                inset: 0;
                background-image: url('{{ $backgroundPath }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }

        @endif

        /* ── Content area ── */
        .diploma-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            padding: 14mm 14mm 10mm 14mm;
        }

        /* ── Top: org block (right-aligned) ── */
        .org-block {
            text-align: right;
            margin-bottom: 8mm;
        }

        .org-name {
            /* font-family: 'Arial Black Custom', sans-serif; */
            font-size: 10pt;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            /* letter-spacing: 0.04em;
            line-height: 1; */
        }

        .contest-title {
            /* font-family: 'Calibri Custom', sans-serif; */
            /* Added Calibri */
            font-size: 16pt;
            font-weight: bold;
            color: #763609;
            /* line-height: 1;
            margin-top: 2mm; */
        }

        .org-city {
            /* font-family: 'Calibri Custom', sans-serif; */
            /* Added Calibri */
            font-size: 10pt;
            font-weight: bold;
            color: #000000;
            line-height: 1;
            margin-top: 1mm;
        }

        /* ── Diploma word ── */
        .diploma-word {
            text-align: right;
            font-size: 56pt;
            margin-top: 60px; 
            color: #ca9848;
            font-weight: bold;
            font-style: italic;
            /* line-height: 1.0; */
            letter-spacing: 0.01em;
            margin-bottom: -2mm;
        }

        /* ── Degree ── */
        .diploma-degree {
            text-align: right;
            font-size: 22pt;
            font-weight: bold;
            color: #aa8345;
            margin-bottom: 2mm;
            /* line-height: 1.2; */
        }

        /* ── Gold divider ── */
        .gold-divider {
            width: 60mm;
            height: 2px;
            background: linear-gradient(90deg, transparent, #A67C00, transparent);
            margin: 2mm 0 2mm auto;
        }

        /* ── Awarded label ── */
        .awarded-label {
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
            color: #aa8345;
            letter-spacing: 0em;
            margin-bottom: 1mm;
        }

        /* ── Participant name ── */
        .participant-last-name {
            text-align: right;
            font-size: 21pt;
            font-weight: bold;
            font-style: italic;
            color: #000000;
            line-height: 1.2;
        }

        .participant-first-patronymic {
            text-align: right;
            font-size: 21pt;
            font-weight: bold;
            font-style: italic;
            color: #000000;
            line-height: 1.2;
            margin-bottom: 5mm;
        }

        /* ── Details section ── */
        .details-block {
            text-align: right;
            margin-bottom: 4mm;
        }

        .nomination-line {
            font-size: 12pt;
            font-weight: 600;
            color: #000000;
            line-height: 1.7;
        }

        .nomination-label {
            font-size: 12pt;
            font-weight: normal;
            color: #aa8345;
        }

        .age-line {
            font-size: 12pt;
            font-weight: 600;
            color: #000000;
            line-height: 1.7;
        }

        .age-label {
            font-size: 12pt;
            font-weight: normal;
            color: #aa8345;
        }

        .teacher-line {
            font-size: 12pt;
            font-weight: 600;
            color: #000000;
            line-height: 1.7;
        }

        .teacher-label {
            font-size: 12pt;
            font-weight: normal;
            color: #aa8345;
        }

        /* ── Bottom blocks (absolutely positioned) ── */
        .bottom-left-block {
            position: absolute;
            bottom: 10mm;
            left: 14mm;
            width: 85mm;
        }

        .bottom-right-block {
            position: absolute;
            bottom: 10mm;
            right: 14mm;
        }

        .jury-label {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
        }

        .jury-member {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
        }

        .diploma-meta {
            font-size: 10pt;
            font-weight: normal;
            color: #000000;
            margin-top: 4mm;
        }

        .diploma-contacts {
            font-size: 9pt;
            font-weight: normal;
            color: #000000;
            margin-top: 4mm;
        }

        .qr-code {
            width: 38mm;
            height: 38mm;
            display: inline-block;
        }

        .qr-code img {
            width: 38mm;
            height: 38mm;
        }
    </style>
</head>

<body>
    <div class="diploma-page">
        @if($backgroundPath)
            <div class="diploma-bg"></div>
        @endif

        <!-- Main content -->
        <div class="diploma-content">

            <!-- Organisation + contest (right-aligned) -->
            <div class="org-block">
                <div class="org-name">{{ $orgName }}</div>
                @if($orgCity)
                    <div class="org-city">г.{{ $orgCity }}</div>
                @endif
                <div class="contest-title">{{ $contestTitle }}</div>
            </div>

            <!-- Diploma word -->
            <div class="diploma-word">Диплом</div>

            <!-- Degree -->
            <div class="diploma-degree">{{ $statusLabel }}</div>

            <div class="gold-divider"></div>

            <!-- награждается -->
            <div class="awarded-label">награждается</div>

            <!-- Participant name -->
            <div class="participant-last-name">{{ $participantLastName }}</div>
            <div class="participant-first-patronymic">{{ $participantFirstPatronymic }}</div>

            <!-- Details (right-aligned) -->
            <div class="details-block">
                @if($categoryName)
                    <div class="nomination-line">
                        <span class="nomination-label">номинация: </span>«{{ $categoryName }}»
                    </div>
                @endif
                @if($ageGroupName)
                    <div class="age-line">
                        <span class="age-label">возрастная категория: </span>{{ $ageGroupName }}
                    </div>
                @endif
                @if($teacherName)
                    <div class="teacher-line">
                        <span class="teacher-label">Преподаватель: </span>{{ $teacherName }}
                    </div>
                @endif
            </div>


        </div>

        <!-- Bottom-left: jury + diploma number + contacts (fixed) -->
        <div class="bottom-left-block">
            @if(!empty($juryMembers))
                <div class="jury-label">Жюри:</div>
                @foreach($juryMembers as $juryMember)
                    <div class="jury-member">{{ $juryMember }}</div>
                @endforeach
            @endif

            <div class="diploma-meta">
                Диплом № {{ $diplomaNumber }}<br>
                {{ $awardedDate }}
            </div>
            <div class="diploma-contacts">
                talant-centr.ru<br>
                {{ $contactEmail }}
            </div>
        </div>

        <!-- Bottom-right: QR code (fixed) -->
        <div class="bottom-right-block">
            <div class="qr-code">
                <img src="{{ $qrCodeDataUri }}" alt="QR">
            </div>
        </div>

    </div>
</body>

</html>