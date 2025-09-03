<form action="{{ route('pdf.upload') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="file" name="pdf" accept="application/pdf" required>
  <button type="submit">Importer PDF</button>
</form>
<div id="table-pdf"></div>
 
 @if(isset($pdf))
<script>
  const table = new Tabulator("#table-pdf", {
    ajaxURL: "/api/pdf/{{ $pdf->id }}/rows",
    height: "500px",
    layout: "fitColumns",
    columns: [
      { title: "Col1", field: "col1", editor: "input" },
      { title: "Col2", field: "col2", editor: "input" },
      // …
    ],
  });
</script>
@endif

 
