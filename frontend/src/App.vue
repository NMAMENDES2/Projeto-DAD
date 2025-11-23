<script setup>
  import { RouterView } from 'vue-router';
  import { useSocketStore } from './stores/socket';
  import { onMounted } from 'vue';

  const socketStore = useSocketStore()

  onMounted(() => {
  socketStore.handleConnection()
})

</script>

<template>
  <main>
    <router-view/>
  </main>
</template>

<style scoped></style>
