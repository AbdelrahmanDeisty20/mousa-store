<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $projectName }} - Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
@php
    $isReset = str_contains($purpose, 'forgot_password');
    $displayName = trim($recipientName);
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f5f7;padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.06);">
                <!-- Header -->
                <tr>
                    <td align="center" style="background-color:#111827;padding:24px 20px;color:#ffffff;">
                        <h1 style="margin:0;font-size:24px;letter-spacing:0.5px;">{{ $projectName }}</h1>
                        <p style="margin:6px 0 0;font-size:13px;color:#d1d5db;">موسى ستور للتسوق الإلكتروني</p>
                    </td>
                </tr>

                <!-- Arabic Section -->
                <tr>
                    <td dir="rtl" align="right" style="padding:28px 28px 18px;text-align:right;">
                        <h2 style="margin:0 0 10px;font-size:19px;color:#111827;">
                            {{ $displayName ? "أهلاً {$displayName}،" : 'أهلاً بك،' }}
                        </h2>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#374151;">
                            @if($isReset)
                                تم طلب رمز تحقق لاستعادة كلمة المرور الخاصة بحسابك في <strong>{{ $projectName }}</strong>. استخدم الكود التالي لإكمال العملية:
                            @else
                                شكراً لتسجيلك في <strong>{{ $projectName }}</strong>. استخدم كود التحقق التالي لتفعيل حسابك:
                            @endif
                        </p>

                        <div style="margin:18px 0;padding:16px;background-color:#f9fafb;border:1px dashed #dc2626;border-radius:8px;text-align:center;">
                            <span style="display:inline-block;font-size:30px;font-weight:bold;letter-spacing:6px;color:#dc2626;">{{ $otpCode }}</span>
                        </div>

                        <p style="margin:0;font-size:13px;color:#6b7280;">
                            هذا الكود صالح لمدة <strong>{{ $expiresInMinutes }} دقائق</strong> فقط. إذا لم تقم بهذا الطلب، يمكنك تجاهل هذه الرسالة.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px;">
                        <hr style="border:none;border-top:1px solid #e5e7eb;margin:0;">
                    </td>
                </tr>

                <!-- English Section -->
                <tr>
                    <td dir="ltr" align="left" style="padding:18px 28px 28px;text-align:left;">
                        <h2 style="margin:0 0 10px;font-size:18px;color:#111827;">
                            {{ $displayName ? "Hello {$displayName}," : 'Hello,' }}
                        </h2>
                        <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#374151;">
                            @if($isReset)
                                You requested a verification code to reset your password on <strong>{{ $projectName }}</strong>. Please use the OTP code below:
                            @else
                                Thank you for registering with <strong>{{ $projectName }}</strong>. Please use the OTP code below to verify your account:
                            @endif
                        </p>

                        <div style="margin:16px 0;padding:14px;background-color:#f9fafb;border:1px dashed #dc2626;border-radius:8px;text-align:center;">
                            <span style="display:inline-block;font-size:28px;font-weight:bold;letter-spacing:6px;color:#dc2626;">{{ $otpCode }}</span>
                        </div>

                        <p style="margin:0;font-size:13px;color:#6b7280;">
                            This code is valid for <strong>{{ $expiresInMinutes }} minutes</strong>. If you did not request this, you can safely ignore this email.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="background-color:#f9fafb;padding:16px 20px;font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;">
                        &copy; {{ date('Y') }} {{ $projectName }}. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
