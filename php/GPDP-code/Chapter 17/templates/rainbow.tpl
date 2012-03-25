<html><head>
<title>{$title}</title>
</head><body>
<h1>{$title}</h1>
<ol>
  {section name=rainbow loop=$colors}
    <li>{$colors[rainbow]}</li>
  {/section}
</ol>
</body></html>
