<script lang="ts">
  import { Button } from 'flowbite-svelte';
  import Node from './ServiceBodiesTreeNode.svelte';
  import type { ServiceBody } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';

  interface TreeNode {
    label: string;
    value: string;
    checked?: boolean;
    indeterminate?: boolean;
    expanded?: boolean;
    children?: TreeNode[];
  }

  export let serviceBodies: ServiceBody[];
  export let selectedValues: string[] = [];

  const treeMap: Record<string, TreeNode> = {};
  let trees: TreeNode[] = convertServiceBodiesToTreeNodes(serviceBodies);

  function convertServiceBodiesToTreeNodes(serviceBodies: ServiceBody[]): TreeNode[] {
    const nodeMap: { [key: number]: TreeNode } = {};
    const roots: TreeNode[] = [];
    serviceBodies.forEach((sb) => {
      nodeMap[sb.id] = {
        label: sb.name,
        value: sb.id.toString(),
        checked: selectedValues.includes(sb.id.toString()),
        expanded: true,
        children: []
      };
    });
    serviceBodies.forEach((sb) => {
      const node = nodeMap[sb.id];
      if (sb.parentId && nodeMap[sb.parentId]) {
        nodeMap[sb.parentId].children!.push(node);
      } else {
        roots.push(node);
      }
    });

    return roots;
  }

  function initTreeMap(trees: TreeNode[]) {
    trees.forEach((tree) => {
      processTree(tree);
    });
  }

  function processTree(node: TreeNode) {
    if (node.children) {
      for (const child of node.children) {
        treeMap[child.label] = node;
        processTree(child);
      }
    }
  }

  initTreeMap(trees);

  function rebuildChildren(node: TreeNode, checkAsParent = true) {
    if (node.children) {
      for (const child of node.children) {
        if (checkAsParent) child.checked = !!node.checked;
        rebuildChildren(child, checkAsParent);
      }
      node.indeterminate = node.children.some((c) => c.indeterminate) || (node.children.some((c) => !!c.checked) && node.children.some((c) => !c.checked));
    }
  }

  function rebuildTree(e: CustomEvent<{ node: TreeNode }>, checkAsParent: boolean = true): void {
    const node = e.detail.node;
    let parent = treeMap[node.label];
    rebuildChildren(node, checkAsParent);
    while (parent) {
      const allCheck = parent.children?.every((c) => !!c.checked);
      if (allCheck) {
        parent.indeterminate = false;
        parent.checked = true;
      } else {
        const haveCheckedOrIndetermine = parent.children?.some((c) => !!c.checked || c.indeterminate);
        if (haveCheckedOrIndetermine) {
          parent.indeterminate = true;
        } else {
          parent.indeterminate = false;
        }
      }

      parent = treeMap[parent.label];
    }
    trees = [...trees];
    selectedValues = collectSelectedValues(trees);
  }

  function collectSelectedValues(trees: TreeNode[]): string[] {
    const selected: string[] = [];

    function traverse(node: TreeNode) {
      if (node.checked) {
        selected.push(node.value);
      }
      if (node.children) {
        node.children.forEach(traverse);
      }
    }

    trees.forEach(traverse);
    return selected;
  }

  function selectAll() {
    trees.forEach((node) => {
      checkAllNodes(node, true);
    });
    trees = [...trees];
    selectedValues = collectSelectedValues(trees);
  }

  function unselectAll() {
    trees.forEach((node) => {
      checkAllNodes(node, false);
    });
    trees = [...trees];
    selectedValues = [];
  }

  function checkAllNodes(node: TreeNode, check: boolean) {
    node.checked = check;
    node.indeterminate = false;
    if (node.children) {
      node.children.forEach((child) => checkAllNodes(child, check));
    }
  }
  function toggleAll() {
    if (isAllSelected) {
      unselectAll();
    } else {
      selectAll();
    }
  }

  $: isAllSelected = trees.every((node) => isNodeFullySelected(node));
  function isNodeFullySelected(node: TreeNode): boolean {
    return !!node.checked && (!node.children || node.children.every(isNodeFullySelected));
  }
</script>

<div class="mb-4">
  <Button on:click={toggleAll} size="xs" color="primary" class="w-full">
    {isAllSelected ? $translations.unselectAllServiceBodies : $translations.selectAllServiceBodies}
  </Button>
</div>

<div>
  {#each trees as tree}
    <Node {tree} on:toggle={rebuildTree} />
  {/each}
</div>
