<script lang="ts">
  /*global settings */

  import { theme } from '../stores/themeStore';
  import ThemeSwitcher from '../components/ThemeSwitcher.svelte';

  let version = settings.version;

  if (typeof window !== 'undefined') {
    const savedTheme = window.localStorage.getItem('theme');
    if (savedTheme) {
      theme.set(savedTheme);
    }
  }
</script>

<section class={$theme}>
  <div id="export-form">
    <h1>BMLT Root Server {version}</h1>
    <div id="footer">
      <a href="#/login" class="footer-link">Login</a>
    </div>
    <ThemeSwitcher />
  </div>
</section>
