<template>
  <mavon-editor :model-value="modelValue" @change="handleChange" />
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  modelValue: String
})
const emit = defineEmits(['update:modelValue'])

function handleChange(val) {
  emit('update:modelValue', val)
}
</script>