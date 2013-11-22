<html>
<head>
<script type="text/javascript"
  src="dygraph/dygraph-dev.js"></script>
</head>
<body>
<div class="plotcontainer" id="graphdiv"></div>
<script type="text/javascript">
   g = new Dygraph(

		   // containing div
		   document.getElementById("graphdiv"),

		   // CSV or path to a CSV file.
    "Date,Temperature\n" +
    "2008-05-07,75\n" +
    "2008-05-08,70\n" +
    "2008-05-09,80\n"

		   );
</script>
</body>
</html>