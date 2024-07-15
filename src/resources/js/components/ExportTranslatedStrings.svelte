<script lang="ts">
  import { A } from 'flowbite-svelte';

  import * as XLSX from 'xlsx';
  import { translations } from '../stores/localization';

  translations.setLanguage('en');
  let data = Object.entries($translations).filter(([, value]) => typeof value === 'string');

  let downloadUrl: string = exportCSV(data);

  function processExportData(data: any[]): any[] {
    return data.map((row) =>
      Object.keys(row).reduce((acc, key) => {
        acc[key] = row[key];
        return acc;
      }, {} as any)
    );
  }

  function exportCSV(data: any[]): string {
    const processedData = processExportData(data);
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(processedData);
    XLSX.utils.book_append_sheet(wb, ws, 'Data');
    const csvString = XLSX.write(wb, { bookType: 'csv', type: 'string' });
    const blob = new Blob([csvString], { type: 'text/csv' });
    return URL.createObjectURL(blob);
  }
</script>

<A class="font-medium hover:underline" href={downloadUrl} download="BMLT_Translation_Strings.csv">Download Translation Strings</A>
