<html>
<head>
<script type="text/javascript"
  src="dygraph/dygraph-dev.js"></script>
</head>
<body>
<div class="plotcontainer" id="graphdiv"></div>
<script type="text/javascript">
   g = new Dygraph(
		   document.getElementById("graphdiv"),

		   //DATA
    "2.0,-1.5963085457e-10\n" +
    "8.0,-1.3233493443e-10\n" +
    "14.0,-1.6229403155e-10\n" +
    "20.0,-1.6933336726e-10\n" +
    "26.0,-1.4804439076e-10\n" +
    "32.0,-1.1281154564e-10\n" +
    "38.0,-1.4738024045e-10\n" +
    "44.0,-1.188283529e-10\n" +
    "50.0,-1.0256982181e-10\n" +
    "56.0,-1.043978791e-10\n" +
    "62.0,-7.906810691e-11\n" +
    "68.0,-9.27851123e-11\n" +
    "53252.0,4.41279475554e-07\n" +
    "53258.0,4.4134361209e-07\n" +
		   "53264.0,4.41486318924e-07\n",

		   {
		   labels: ['Date', '2166'],
		       logscale: false,
		       title: 'Mass vs. time',
		       ylabel: 'left',
		       yAxisLabelWidth: 60
		       }
		   );
</script>
</body>
</html>