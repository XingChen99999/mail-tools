import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import { loadEnv } from 'vite'
import type { UserConfig, ConfigEnv, ProxyOptions } from 'vite'
import { isProd, customHotUpdate } from '/@/utils/vite'
import { svgBuilder } from '/@/components/icon/svg/index'
import fs from 'fs';
import path from 'path';

const pathResolve = (dir: string): any => {
    return resolve(__dirname, '.', dir)
}

// https://vitejs.cn/config/
const viteConfig = ({ mode }: ConfigEnv): UserConfig => {
    const { VITE_PORT, VITE_OPEN, VITE_BASE_PATH, VITE_OUT_DIR, VITE_PROXY_URL } = loadEnv(mode, process.cwd())

    const alias: Record<string, string> = {
        '/@': pathResolve('./src/'),
        assets: pathResolve('./src/assets'),
        'vue-i18n': isProd(mode) ? 'vue-i18n/dist/vue-i18n.cjs.prod.js' : 'vue-i18n/dist/vue-i18n.cjs.js',
    }

    let proxy: Record<string, string | ProxyOptions> = {}
    if (VITE_PROXY_URL) {
        proxy = {
            '/': {
                target: VITE_PROXY_URL,
                changeOrigin: true,
            },
        }
    }

    // console.log('proxy: ', proxy)
    return {
        plugins: [
            vue(), svgBuilder('./src/assets/icons/'), customHotUpdate()
            ,{
                name: 'clean-assets-dir',
                buildStart() {
                    // 获取资源目录的路径
                    // console.log(VITE_OUT_DIR)
                    // console.log(mode)
                    if (mode !== 'development'){
                        const assetsDir = path.resolve(VITE_OUT_DIR, 'assets');
                        console.log(`资源目录路径：${assetsDir}`);
                        // 检查目录是否存在，存在则清空
                        if (fs.existsSync(assetsDir)) {
                            fs.readdirSync(assetsDir).forEach((file) => {
                                const filePath = path.resolve(assetsDir, file);
                                fs.unlinkSync(filePath);
                            });
                            console.log(`清空目录：${assetsDir}`);
                        }
                    }

                }
            }
        ],
        root: process.cwd(),
        resolve: { alias },
        base: VITE_BASE_PATH,
        server: {
            port: parseInt(VITE_PORT),
            open: VITE_OPEN != 'false',
            proxy: proxy,
        },
        build: {
            cssCodeSplit: false,
            sourcemap: false,
            outDir: VITE_OUT_DIR,
            emptyOutDir: false,
            chunkSizeWarningLimit: 1500,
            copyPublicDir: false,
            rollupOptions: {
                output: {
                    manualChunks: {
                        // 分包配置，配置完成自动按需加载
                        vue: ['vue', 'vue-router', 'pinia', 'vue-i18n', 'element-plus'],
                        echarts: ['echarts'],
                    },
                },
            },
        },
    }
}

export default viteConfig
