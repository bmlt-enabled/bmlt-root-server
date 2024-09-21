<script lang="ts">
  import { Checkbox, Label } from 'flowbite-svelte';

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
  <li>
    {#if tree.children}
      <div class="flex items-center space-x-2">
        {#if tree.children.length > 0}
          <button type="button" on:click={toggleExpansion} class="arrow" class:arrowDown={tree.expanded} aria-expanded={tree.expanded} aria-label="Toggle node"></button>
        {/if}
        <Checkbox id={tree.value} data-label={tree.label} checked={tree.checked} indeterminate={tree.indeterminate} on:click={toggleCheck} />
        <Label for={tree.value} class="ml-2">{tree.label}</Label>
      </div>
      {#if tree.expanded}
        <ul>
          {#each tree.children as child}
            <li>
              <svelte:self tree={child} on:toggle />
            </li>
          {/each}
        </ul>
      {/if}
    {:else}
      <div class="flex items-center space-x-2">
        <Checkbox data-label={tree.label} checked={tree.checked} indeterminate={tree.indeterminate} on:click={toggleCheck} />
        <Label for={tree.label} class="ml-2">{tree.label}</Label>
      </div>
    {/if}
  </li>
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
