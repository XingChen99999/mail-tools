<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '':'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem :label="t('message.email_id')" type="remoteSelect" v-model="baTable.form.items!.email_id" prop="email_id" :input-attr="{ pk: 'email.id', field: 'address', remoteUrl: '/admin/Email/index' }" :placeholder="t('Please select field', { field: t('message.email_id') })" />
                    <FormItem :label="t('message.form')" type="string" v-model="baTable.form.items!.form" prop="form" :placeholder="t('Please input field', { field: t('message.form') })" />
                    <FormItem :label="t('message.to')" type="string" v-model="baTable.form.items!.to" prop="to" :placeholder="t('Please input field', { field: t('message.to') })" />
                    <FormItem :label="t('message.title')" type="string" v-model="baTable.form.items!.title" prop="title" :placeholder="t('Please input field', { field: t('message.title') })" />
                    <FormItem :label="t('message.content')" type="editor" v-model="baTable.form.items!.content" prop="content" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :placeholder="t('Please input field', { field: t('message.content') })" />
                    <FormItem :label="t('message.is_read')" type="select" v-model="baTable.form.items!.is_read" prop="is_read" :input-attr="{ content: { '0': t('message.is_read 0'), '1': t('message.is_read 1') } }" :placeholder="t('Please select field', { field: t('message.is_read') })" />
                    <FormItem :label="t('message.code')" type="string" v-model="baTable.form.items!.code" prop="code" :placeholder="t('Please input field', { field: t('message.code') })" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import type { FormItemRule } from 'element-plus'
import { inject, reactive, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import type baTableClass from '/@/utils/baTable'
import { buildValidatorData } from '/@/utils/validate'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    content: [buildValidatorData({ name: 'editorRequired', title: t('message.content') })],
    create_time: [buildValidatorData({ name: 'date', title: t('message.create_time') })],
})
</script>

<style scoped lang="scss"></style>
