<template>
  <g>
    <line
      v-if="level > 0"
      :x1="parentX + nodeWidth / 2"
      :y1="parentY + nodeHeight"
      :x2="x + nodeWidth / 2"
      :y2="y"
      stroke="#ccc"
      stroke-width="2"
    />

    <rect
      :x="x"
      :y="y"
      :width="nodeWidth"
      :height="nodeHeight"
      :fill="node.color || '#F0F0F0'"
      rx="8"
      ry="8"
      stroke="var(--node-border-color)"
      stroke-width="1"
    />

    <text
      :x="x + nodeWidth / 2"
      :y="y + nodeHeight / 2 - 10"
      text-anchor="middle"
      alignment-baseline="middle"
      font-weight="bold"
      fill="#333"
    >
      {{ node.name }}
    </text>

    <text
      :x="x + nodeWidth / 2"
      :y="y + nodeHeight / 2 + 10"
      text-anchor="middle"
      alignment-baseline="middle"
      fill="#333"
    >
      {{ node.value }}
    </text>

    <template v-if="node.children && node.children.length > 0">
      <FunnelingB2CNode
        v-for="(child, index) in node.children"
        :key="index"
        :node="child"
        :x="getChildX(index, node.children.length)"
        :y="y + nodeHeight + verticalSpacing"
        :parentX="x"
        :parentY="y"
        :level="level + 1"
        :nodeWidth="nodeWidth"
        :nodeHeight="nodeHeight"
        :horizontalSpacing="horizontalSpacing"
        :verticalSpacing="verticalSpacing"
      />
    </template>
  </g>
</template>

<script setup>
import { defineProps, computed } from 'vue';

// Definisikan props yang akan diterima komponen
const props = defineProps({
  node: Object, // Objek node saat ini (e.g., { name: 'RE', value: 103, children: [...] })
  x: Number,    // Posisi X node
  y: Number,    // Posisi Y node
  parentX: Number, // Posisi X node induk (untuk menggambar garis)
  parentY: Number, // Posisi Y node induk (untuk menggambar garis)
  level: Number,   // Level kedalaman node dalam pohon (0 untuk root)
  nodeWidth: Number, // Lebar kotak node
  nodeHeight: Number, // Tinggi kotak node
  horizontalSpacing: Number, // Jarak horizontal antar node di level yang sama
  verticalSpacing: Number,   // Jarak vertikal antar level
});

// Fungsi untuk menghitung posisi X anak agar terpusat di bawah induk
const getChildX = (childIndex, totalChildren) => {
  const totalChildrenWidth = totalChildren * props.nodeWidth + (totalChildren - 1) * props.horizontalSpacing;
  const startX = props.x + (props.nodeWidth / 2) - (totalChildrenWidth / 2);
  return startX + (childIndex * (props.nodeWidth + props.horizontalSpacing));
};
</script>

<style scoped>
/* Tidak ada style spesifik yang dibutuhkan di sini, semua diatur oleh atribut SVG */
</style>