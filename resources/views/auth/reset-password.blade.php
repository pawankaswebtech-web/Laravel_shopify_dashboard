<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="20" cellspacing="0" style="background: #ffffff; border-radius: 8px;">
                    
                    <tr>
                        <td align="center">
                            <h2 style="color: #333;">Reset Your Password</h2>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <p>Hello,</p>

                            <p>
                                We received a request to reset your password. Click the button below to reset it.
                            </p>

                            <p style="text-align: center;">
                                <a href="{{ $link }}" 
                                   style="background-color: #0d6efd; 
                                          color: #ffffff; 
                                          padding: 12px 25px; 
                                          text-decoration: none; 
                                          border-radius: 5px; 
                                          display: inline-block;">
                                    Reset Password
                                </a>
                            </p>

                            <p>
                                If you did not request a password reset, please ignore this email.
                            </p>

                            <p>Thanks,<br>
                            {{ config('app.name') }}</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>