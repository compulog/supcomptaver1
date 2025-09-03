<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Test Tabulator</title>
  <link href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator.min.css" rel="stylesheet">
</head>
<body>
<div id="test-table"></div>
<script src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
<script>
  const data = [
    {id: 1, name: "Alice", montant: 100},
    {id: 2, name: "Bob", montant: 200},
  ];
  const table = new Tabulator("#test-table", {
    data: data,
    layout: "fitColumns",
    columns: [
      {title: "Nom", field: "name"},
      {title: "Montant", field: "montant"},
    ],
   rowFormatter:function(row){
    row.getElement().addEventListener("click", function(){
      alert("Ligne cliquée : " + JSON.stringify(row.getData()));
    });
  }
  });
</script>
</body>
</html>