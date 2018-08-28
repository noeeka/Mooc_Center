<style lang="less">
    @import './special_subject-add.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="专题名称">
                            <Input v-model="title" icon="android-list"/>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="专题描述">
                            <textarea name="remark" v-model="remark" id="remark" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitSpe">Submit</Button>
                            <Button type="ghost" style="margin-left: 8px" @click="handleBack">Cancel</Button>
                        </FormItem>
                    </Form>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import axios from 'axios';
    // import tinymce from 'tinymce';

    export default {
        name: 'video-edit',
        data: function () {
            return {
                title: '',
                remark: '',
                status: '1'
            };
        },
        created: function () {
        },
        methods: {
            request: function (params, callback1) {
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {params: params}
                ).then(callback1).catch();
            },
            SubmitSpe: function () {
                var that = this;
                if (this.title.length === 0) {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                that.request(
                    {
                        user_type: 3,
                        api: '/v1/special_subject/save',
                        title: this.title,
                        remark: this.remark,
                        status: this.status
                    },
                    function (response) {
                        response = response.data;
                        if (response.status === 1) {
                            that.$Modal.info({
                                title: '添加',
                                content: '添加成功',
                                onOk: function () {
                                    that.$router.push({name: 'special_subject_index'});
                                }
                            });
                        } else {
                            that.$Message.error(response.msg);
                        }
                    }
                );
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        }
    };
</script>
