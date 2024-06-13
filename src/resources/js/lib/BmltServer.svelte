<script lang="ts">
  import { theme } from '../stores/themeStore';
  import ThemeSwitcher from '../components/ThemeSwitcher.svelte';

  if (typeof window !== 'undefined') {
    const savedTheme = window.localStorage.getItem('theme');
    if (savedTheme) {
      theme.set(savedTheme);
    }
  }
</script>

<section class={$theme}>
  <div id="export-form">
    <h1>BMLT Root Server</h1>
    <div id="footer">
      <a href="https://github.com/bmlt-enabled/bmlt-root-server/issues/new" class="footer-link" target="_blank">Issues?</a>
    </div>
    <ThemeSwitcher />
  </div>
</section>
