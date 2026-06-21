import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  base: '/forge2-qualifier-moc/',
  plugins: [react()],
  build: {
    outDir: 'dist'
  }
})