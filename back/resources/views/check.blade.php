<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Переадресация...</title>
	<link rel="icon" href="/images/favicon.ico">
</head>

<body>

	<form action=<?php  echo $pg_3d_acsurl;?>
		method="POST">
		<input hidden name="MD" value=<?php  echo $pg_3d_md;?> />
		<input hidden name="PaReq" value=<?php  echo $pg_3d_pareq;?> />
		<input hidden name="TermUrl" value=<?php  echo $TermUrl;?> />
		<p data-translate="redirect1">Вы будете перенаправлены на следующую страницу.</p>
		<p data-translate="redirect2">Если этого не произошло, нажмите на кнопку "Далее".</p>
		<button data-translate="next" type="submit" onClick="this.disabled=true; this.value='Ожидайте…';">Далее</button>
	</form>
	<script>
		var translations = {
        'en': {
            'redirect1': 'You will be redirected to next page.',
            'redirect2': 'If this did not happen, click on the "Next" button',
            'next': 'Next',
        },
        'ru': {
            'redirect1': 'Вы будете перенаправлены на следующую страницу.',
            'redirect2': 'Если этого не произошло, нажмите на кнопку "Далее".',
            'next': 'Далее',
        },
    };
    var language = window.navigator.language.split('-')[0];
    if (typeof translations[language] !== 'undefined') {
        document.querySelectorAll('[data-translate]').forEach(function(el) {
            el.innerHTML = translations[language][el.dataset.translate];
        })
    }
	</script>
	<script>
		document.forms[0].submit()
	</script>
</body>

</html>