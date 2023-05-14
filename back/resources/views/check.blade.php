<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Переадресация...</title>
	<link rel="icon" href="/images/favicon.ico">
</head>

<body>
	<h1> Hello <?php echo $name; echo $id;?> </h1>
	<form action="https://payment-3ds.com/payin-3ds-spa/acs?threeDSServerTransID=4aba525a-121e-42c5-8bbe-8f3a4e6eae81"
		method="POST">
		<input hidden name="MD" value="" />
		<input hidden name="PaReq" value="eJydkU&#43;PokAQxb8L50Gaf0ZMzCQbRTGAKzZgc9k0bY80dgOBRsHNfvfF7GHnMNnDniqpqvfy6lc/FTlU3kVZKoYJ5qa1cHQAbKC8Kawz152x4ezKck6VpWx7&#43;qY0bS1rUvOEth2rq5dups9e&#43;7JoKV2fTrS90xa2uOq89TS2cI5tw8aqbuhUtQxiq4s8p&#43;riw8QWnVNMF/pfdUBlUV/iyJ&#43;UhZRNt9Q089Kpl06fCdaSuqookbO21wR&#43;b3CLxQrvIkB2wdwfnQKZQY/SQaB0X&#43;Si60kV31EaPn3DfeDT4pntgtE3o5qIhEduGGbnqM6N4&#43;qLAH/u&#43;BSjwaOglVSnODNSCw03TLvrGuFs6mpXLAvavn8FYfU/CNZY4u&#43;Y3OjrNXTcA7zb8yyOIEwTgLfODQoH4DRrkOFO1emTZB94Zc0&#43;4XigM5dZagMfhjcy2mVugDs6f2t8Ew0TkpKMTp&#43;bUZMJXqJz1OSG9eOyLcYsTTaxHnJSZZzwaESp/TzFRyfcugzBeAzTjQygN2QnAAIR6D4sGBKxPGzRE8GIhyIpMngdPPZg//BjB&#43;aB16tC6OnoBIagdLkPjyN6vrz2bPK3sjIswm1sZKnLD&#43;sN&#43;zgqv34D0zTmKw==" />
		<input hidden name="TermUrl" value="https://secure.paybox.money/v2/user/term-url/92009584-237a-469e-b76c-dd8a2a00b31d" />
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
		//document.forms[0].submit()
	</script>
</body>

</html>