<script lang="ts">
  import { Checkbox, Li } from 'flowbite-svelte';

  import { createEventDispatcher } from 'svelte';

  interface TreeNode {
    label: string;
    value: string;
    checked?: boolean;
    indeterminate?: boolean;
    expanded?: boolean;
    children?: TreeNode[];
  }

  export let tree: TreeNode;

  const dispatch = createEventDispatcher<{ toggle: { node: TreeNode } }>();
  const toggleExpansion = () => {
    tree.expanded = !tree.expanded;
  };

  const toggleCheck = () => {
    tree.checked = !tree.checked;
    dispatch('toggle', {
      node: tree
    });
  };
</script>

<ul>
  <Li>
    {#if tree.children}
      <Checkbox data-label={tree.label} checked={tree.checked} indeterminate={tree.indeterminate} on:click={toggleCheck}
        >{tree.label}&nbsp;<button type="button" on:click={toggleExpansion} class="arrow" class:arrowDown={tree.expanded} aria-expanded={tree.expanded} aria-label="Toggle node"></button>
      </Checkbox>
      {#if tree.expanded}
        {#each tree.children as child}
          <svelte:self tree={child} on:toggle />
        {/each}
      {/if}
    {:else}
      <Checkbox data-label={tree.label} checked={tree.checked} indeterminate={tree.indeterminate} on:click={toggleCheck}>{tree.label}</Checkbox>
      <button type="button" on:click={toggleCheck} aria-label="Toggle check"></button>
    {/if}
  </Li>
</ul>

<style>
  ul {
    margin: 0;
    list-style: none;
    padding-left: 1.2rem;
    user-select: none;
  }

  .arrow::before {
    --tw-content: '+';
    content: var(--tw-content);
    display: inline-block;
    cursor: pointer;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
    font-size: 1rem;
    line-height: 1.5rem;
  }

  .arrowDown::before {
    --tw-content: '-';
    content: var(--tw-content);
  }
</style>
