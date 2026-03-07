<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: 'DejaVu Serif', serif;
            background-color: #FAF8F5;
        }

        .diploma-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-color: #FAF8F5;
            overflow: hidden;
            @if($backgroundPath)
            background-image: url('{{ $backgroundPath }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            @endif
        }

        .diploma-overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(250, 248, 245, 0.88);
        }

        .diploma-border {
            position: absolute;
            inset: 8mm;
            border: 2px solid #D4AF37;
        }

        .diploma-border-inner {
            position: absolute;
            inset: 11mm;
            border: 1px solid rgba(212, 175, 55, 0.4);
        }

        .diploma-body {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20mm 25mm;
        }

        .org-name {
            font-size: 11pt;
            color: #9A8B7A;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 6mm;
            font-family: 'DejaVu Serif', serif;
        }

        .award-label {
            font-size: 10pt;
            color: #8B4513;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 3mm;
        }

        .award-title {
            font-size: 28pt;
            font-weight: bold;
            color: #D4AF37;
            margin-bottom: 5mm;
            line-height: 1.1;
        }

        .gold-bar {
            width: 35mm;
            height: 2px;
            background-color: #D4AF37;
            margin: 0 auto 5mm auto;
        }

        .award-text {
            font-size: 10pt;
            color: #9A8B7A;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 3mm;
        }

        .participant-name {
            font-size: 22pt;
            color: #2C2416;
            font-weight: bold;
            margin-bottom: 5mm;
            line-height: 1.2;
        }

        .contest-info {
            font-size: 11pt;
            color: #8B4513;
            margin-bottom: 2mm;
            line-height: 1.5;
        }

        .category-info {
            font-size: 10pt;
            color: #9A8B7A;
            margin-bottom: 3mm;
            font-style: italic;
        }

        .teacher-info {
            font-size: 10pt;
            color: #9A8B7A;
            margin-bottom: 3mm;
        }

        .date-line {
            font-size: 9pt;
            color: #9A8B7A;
            letter-spacing: 0.05em;
        }

        .corner-ornament {
            position: absolute;
            width: 10mm;
            height: 10mm;
            border-color: #D4AF37;
            border-style: solid;
        }

        .corner-tl {
            top: 6mm;
            left: 6mm;
            border-width: 2px 0 0 2px;
        }

        .corner-tr {
            top: 6mm;
            right: 6mm;
            border-width: 2px 2px 0 0;
        }

        .corner-bl {
            bottom: 6mm;
            left: 6mm;
            border-width: 0 0 2px 2px;
        }

        .corner-br {
            bottom: 6mm;
            right: 6mm;
            border-width: 0 2px 2px 0;
        }
    </style>
</head>
<body>
    <div class="diploma-container">
        @if($backgroundPath)
        <div class="diploma-overlay"></div>
        @endif

        <div class="diploma-border"></div>
        <div class="diploma-border-inner"></div>
        <div class="corner-ornament corner-tl"></div>
        <div class="corner-ornament corner-tr"></div>
        <div class="corner-ornament corner-bl"></div>
        <div class="corner-ornament corner-br"></div>

        <div class="diploma-body">
            <div class="org-name">{{ $orgName }}</div>

            <div class="award-label">Диплом</div>
            <div class="award-title">{{ $statusLabel }}</div>
            <div class="gold-bar"></div>

            <div class="award-text">Присуждается</div>
            <div class="participant-name">{{ $participantName }}</div>

            <div class="contest-info">За участие в конкурсе «{{ $contestTitle }}»</div>

            @if($categoryName)
                <div class="category-info">Номинация: {{ $categoryName }}</div>
            @endif

            @if($teacherName)
                <div class="teacher-info">Преподаватель: {{ $teacherName }}</div>
            @endif

            <div class="date-line">{{ $awardedDate }}</div>
        </div>
    </div>
</body>
</html>
