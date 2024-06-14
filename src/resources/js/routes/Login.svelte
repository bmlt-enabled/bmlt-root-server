<script lang="ts">
  import { theme } from '../stores/themeStore';
  import LoginForm from '../components/LoginForm.svelte';
</script>

<section class={$theme}>
  <div id="export-form">
    <h1>Lets Login</h1>
    <div id="footer">
      <LoginForm />
    </div>
  </div>
</section>
