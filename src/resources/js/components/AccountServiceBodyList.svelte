<script lang="ts">
  // FIX THIS -- explain why separate component!
  import { Listgroup } from 'flowbite-svelte';
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';

  import type { ServiceBody, User } from 'bmlt-root-server-client';

  import RootServerApi from '../lib/RootServerApi';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  let serviceBodies: ServiceBody[] = [];
  let serviceBodiesLoaded = false;
  let editableServiceBodyNames: string[] = [];

  export let user: User | null;

  async function getServiceBodies(): Promise<void> {
    try {
      spinner.show();
      serviceBodies = await RootServerApi.getServiceBodies();
      serviceBodiesLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  // helper function to compute the set of service bodies that the currently logged in user can edit.
  // s is the starting service body
  // children is an array of sets of children, indexed by the id of the parent service body
  // editableServiceBodies is the set of service bodies that is being accumulated
  function recursivelyAddServiceBodies(s: ServiceBody, children: Set<ServiceBody>[], editableServiceBodies: Set<ServiceBody>) {
    editableServiceBodies.add(s);
    if (children[s.id]) {
      for (const c of children[s.id]) {
        recursivelyAddServiceBodies(c, children, editableServiceBodies);
      }
    }
  }

  onMount(() => {
    getServiceBodies();
  });

  $: {
    const id = user?.id;
    if (serviceBodiesLoaded && id) {
      const editableServiceBodies: Set<ServiceBody> = new Set();
      if (user?.type === 'admin') {
        serviceBodies.forEach((s) => editableServiceBodies.add(s));
      } else if (user?.type === 'serviceBodyAdmin') {
        // children is an array with indices = service body ids, values a set of children of that service body
        // (not recursively - the recursion is handled elsewhere)
        const children: Set<ServiceBody>[] = [];
        for (const s of serviceBodies) {
          const p = s.parentId;
          if (p) {
            if (children[p]) {
              children[p].add(s);
            } else {
              children[p] = new Set([s]);
            }
          }
        }
        for (const s of serviceBodies) {
          if (s.adminUserId === id || s.assignedUserIds.includes(id)) {
            recursivelyAddServiceBodies(s, children, editableServiceBodies);
          }
        }
      }
      editableServiceBodyNames = Array.from(editableServiceBodies)
        .map((s) => s.name)
        .sort();
    }
  }
</script>

{#if !serviceBodiesLoaded}
  {$translations.loading}
{:else if editableServiceBodyNames.length === 0}
  {$translations.none}
{:else}
  <Listgroup items={editableServiceBodyNames} let:item>
    {item}
  </Listgroup>
{/if}
