<script lang="ts">
  import { theme } from '../stores/themeStore';

  function toggleTheme() {
    theme.update((current) => (current === 'light' ? 'dark' : 'light'));
  }
</script>

<div class="switch-container">
  <button class="switch" on:click={toggleTheme} aria-label="Toggle theme">
    {#if $theme === 'dark'}
      <i class="fas fa-moon icon"></i>
    {:else}
      <i class="fas fa-sun icon"></i>
    {/if}
  </button>
</div>

<style>
  .switch-container {
    display: flex;
    padding-top: 10px;
    justify-content: center;
    align-items: center;
  }

  .switch {
    position: relative;
    display: inline-block;
    margin-bottom: 5px;
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .switch .icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    transition: color 0.3s ease;
  }
</style>
