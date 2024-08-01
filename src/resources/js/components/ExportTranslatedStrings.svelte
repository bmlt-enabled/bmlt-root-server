<script lang="ts">
  import { A } from 'flowbite-svelte';

  import * as XLSX from 'xlsx';
  import { translations } from '../stores/localization';

  let data = translations.getTranslationsForLanguage('en');
  let downloadUrl: string = exportCSV(data);

  function exportCSV(data: Record<string, string>): string {
    const processedData = Object.entries(data).map(([key, value]) => ({
      Key: key,
      Value: value
    }));
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(processedData);
    XLSX.utils.book_append_sheet(wb, ws, 'Data');
    const csvString = XLSX.write(wb, { bookType: 'csv', type: 'string' });
    const blob = new Blob([csvString], { type: 'text/csv' });
    return URL.createObjectURL(blob);
  }
</script>

<A class="font-medium hover:underline" href={downloadUrl} download="BMLT_Translation_Strings.csv">Download Translation Strings</A>
