<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>فرم اموال کاربر {{ $user->name }}</title>
    <style>
        body {
            direction: rtl;
            font-family: 'Tahoma', sans-serif;
            margin: 50px;
            line-height: 1.8;
            background-color: #fff;
        }
        .container {
            border: 1px solid #aaa;
            padding: 40px;
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-evenly;
            text-align: center;
        }
        .signature-box {
            border-top: 1px solid #000;
            width: 25%;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container IranSans">
        <h2>فرم تحویل اموال شرکت</h2>

        <p>
            اینجانب <strong>{{ $user->name }}</strong>، کارمند واحد
            <strong>{{ $user->unit }}</strong> در ساختمان
            <strong>{{ $user->department }}</strong> تحویل اموال ذیل در شرکت را می‌پذیرم.
        </p>

        <p>
            هر نوع نقل و انتقال از اموال تحویلی به اینجانب باید با اطلاع قبلی جمعدار بعمل آید تا در صورت مربوط قید گردد و هرگاه
            کسر و نقصانی در اموال مزبور بوجود آید مکلف هستم مراتب را با گواهی رئیس یا کارمند ارشد واحد ذیربط برای اقدامات لازم
            کتباً به جمعدار اعلام دارم.
        </p>

        {{-- جدول یا لیست اموال متعلق به کاربر (اختیاری) --}}
        @if($approvedAssets->count() > 0)
            <table border="1" cellspacing="0" cellpadding="8" width="100%" style="border-collapse: collapse; margin-top:20px;">
                <thead style="background-color:#eee;">
                    <tr>
                        <th>ردیف</th>
                        <th>شماره اموال</th>
                        <th>شرح کالا</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedAssets as $index => $asset)
                        <tr>
                            <td style="text-align:center;">{{ $index + 1 }}</td>
                            <td style="text-align:center;">{{ $asset->asset_number }}</td>
                            <td>{{ $asset->title }}</td>
                            <td style="text-align:center;">{{ $asset->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="margin-top:20px; color: red;">هیچ مال تایید شده‌ای برای این کاربر یافت نشد.</p>
        @endif

        <div class="signature-section">
            <div class="signature-box">امضا امین اموال</div>
            <div class="signature-box">امضا مسئول خدمات عمومی</div>
            <div class="signature-box">امضا تحویل‌گیرنده</div>
        </div>
    </div>

    {{-- دکمه چاپ --}}
    <button onclick="window.print()"
            class="inline-block bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 transition focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 font-iransans ml-2">
        🖨️ چاپ فرم
    </button>
</body>
</html>
