<style lang="less">
@import './special_subject-edit.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="专题名称">
                            <Input v-model="title" v-if="this.center_id === this.cid" icon="android-list"/>
                            <Input v-model="title" v-else disabled icon="android-list"/>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status" v-if="this.center_id === this.cid">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                            <RadioGroup v-model="status" v-else>
                                <Radio label="1" disabled>启用</Radio>
                                <Radio label="0" disabled >禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="专题描述">
                            <textarea name="remark" v-model="remark" id="remark" cols="180" rows="5" v-if="this.center_id === this.cid"></textarea>
                            <textarea name="remark" v-model="remark" id="remark" cols="180" rows="5" v-else disabled ></textarea>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitSpe" v-if="this.center_id === this.cid">Submit</Button>
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
    import Cookies from 'js-cookie';

    export default {
        name: 'video-edit',
        data: function () {
            return {
                title: '',
                remark: '',
                status: '1',
                cid: 0,
                center_id: 0
            };
        },
        created: function () {
            this.getSpeSubInfo();
            this.cid = Cookies.get('center_id');
        },
        methods: {
            request: function (params, callback1) {
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {params: params}
                ).then(callback1).catch();
            },
            getSpeSubInfo: function () {
                let id = this.$route.params.id;
                let that = this;
                that.request(
                    {
                        user_type: 3,
                        api: '/v1/special_subject/read',
                        id: id
                    },
                    function (response) {
                        response = response.data;
                        if (response.status === 1) {
                            that.title = response.data.title;
                            that.status = response.data.status.toString();
                            that.remark = response.data.remark;
                            that.center_id = response.data.center_id.toString();
                        } else {
                            that.$Message.error(response.msg);
                        }
                    }
                );
            },
            SubmitSpe: function () {
                let id = this.$route.params.id;
                var that = this;
                if (this.title.length === 0) {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                that.request(
                    {
                        user_type: 3,
                        api: '/v1/special_subject/update',
                        title: this.title,
                        remark: this.remark,
                        status: this.status,
                        id: id
                    },
                    function (response) {
                        response = response.data;
                        console.log(response);
                        if (response.status === 1) {
                            that.$Modal.info({
                                title: '修改',
                                content: response.msg,
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
