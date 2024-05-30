<!DOCTYPE html>
<html lang="ko" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,600" rel="stylesheet" type="text/css">
    <style>
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            font-family: 'Roboto', sans-serif !important;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 24px;
            color: #8094ae;
            font-weight: 400;
        }

        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
        }

        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }

        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }

        table table table {
            table-layout: auto;
        }

        a {
            text-decoration: none;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }
    </style>
</head>

<body width="100%"
    style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f5f6fa;">
    <center style="width: 100%; background-color: #f5f6fa;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f5f6fa">
            <tr>
                <td style="padding: 40px 0;">
                    <table style="width:100%;max-width:620px;margin:0 auto;">
                        <tbody>
                            <tr>
                                <td style="text-align: center; padding-bottom:25px">
                                    <a href="#"><img style="height: 40px"
                                            src="{{ asset('assets/images/logo.png') }}" alt="로고"></a>
                                    <p style="font-size: 14px; color: #6576ff; padding-top: 12px;">당신의 도매에 날개를 달아줄</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width:100%;max-width:620px;margin:0 auto;background-color:#ffffff;">
                        <tbody>
                            <tr>
                                <td style="padding: 30px 30px 15px 30px;">
                                    <h2 style="font-size: 18px; color: #6576ff; font-weight: 600; margin: 0;">
                                        저희 도매윙 | 셀윙 파트너스에 대한 관심에 깊이 감사드립니다.
                                    </h2>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0 30px 20px">
                                    <p style="margin-bottom: 10px;">안녕하십니까, {{ $name }} 님.</p>
                                    <p style="margin-bottom: 10px;">아래 문의 내용에 대한 답변을 전해 드립니다.</p>
                                    <p style="margin-bottom: 10px;">
                                        문의 내용:<br>
                                        {{ $question }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0 30px">
                                    <h4
                                        style="font-size: 15px; color: #000000; font-weight: 600; margin: 0; text-transform: uppercase; margin-bottom: 10px">
                                        문의에 대한 답변:
                                    </h4>
                                    <p style="margin-bottom: 10px;">
                                        {{ $answer }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 30px 40px">
                                    <p>본인이 직접 요청한 사안이 아닐 경우, 부디 본 메일을 삭제해주시기 바랍니다.</p>
                                    <p style="margin: 0; font-size: 13px; line-height: 22px; color:#9ea8bb;">이 메일은 자동으로
                                        생성된 메일입니다. 문의사항은 아래 링크를 참조해주십시오.</p>
                                    <a href="{{ url('/') }}">{{ url('/') }}</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width:100%;max-width:620px;margin:0 auto;">
                        <tbody>
                            <tr>
                                <td style="text-align: center; padding:25px 20px 0;">
                                    <p style="font-size: 13px;">Copyright © 2020 JS Korea. All rights reserved. <br>
                                        Powered By <a style="color: #6576ff; text-decoration:none;"
                                            href="https://sellwing.kr">(주) 제이에스코리아</a>.</p>
                                    <p style="padding-top: 15px; font-size: 12px;">
                                        이 메일은 문의 답변을 위해 셀윙 파트너스로부터 작성되었습니다.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
