<style lang="less">
    @import '../../styles/common.less';
    @import './column-edit.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="父栏目">
                            <Select  v-model='parent_id' v-if="cid = center_id">
                                <Option value="0">作为顶级</Option>
                                <Option v-for="(name, id) in columnList" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                            <Select  v-model='parent_id' v-else disabled>
                                <Option value="0">作为顶级</Option>
                                <Option v-for="(name, id) in columnList" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                        </FormItem>
                        <FormItem label="栏目名称">
                            <Input v-model="title" icon="android-list"  v-if="cid = center_id"/>
                            <Input v-model="title" icon="android-list"  v-else disabled/>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status"  v-if="cid = center_id">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                            <RadioGroup v-model="status"  v-else disabled>
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="备注">
                            <textarea name="remark" v-model="remark" id="remark" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitColumn">Submit</Button>
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
        data: function () {
            return {
                parent_id: '0',
                columnList: {},
                title: '',
                remark: '',
                status: '1',
                cid: 0,
                center_id: 0
            };
        },
        created: function () {
            this.getColumnList();
            this.getColumnInfo();
            this.cid = Cookies.get('center_id');
        },
        methods: {
            getColumnList: function () {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/column/getColumnTable',
                                user_type: 3
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.columnList = that.array_map(response.data.list, 'id', 'title');
                    } else {
                        that.columnList = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            getColumnInfo: function () {
                var that = this;
                this.id = this.$route.params.id;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/column/read',
                                id: this.id
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.title = response.data.title;
                        that.remark = response.data.remark;
                        that.status = response.data.status.toString();
                        that.parent_id = response.data.parent_id.toString();
                        that.center_id = response.data.center_id.toString();
                    } else {
                        that.$Message.error(response.msg);
                    }
                }).catch(function (err) {
                    console.log(err.track);
                });
            },
            SubmitColumn: function () {
                var that = this;
                this.id = this.$route.params.id;
                if (this.title.length === 0) {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/column/update',
                                title: this.title,
                                parent_id: this.parent_id,
                                remark: this.remark,
                                status: this.status,
                                id: this.id
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.$Modal.info({
                            title: '修改',
                            content: '修改成功',
                            onOk: function () {
                                that.$router.push({name: 'column_index'});
                            }
                        });
                    } else {
                        that.$Message.error(response.msg);
                    }
                }).catch(function (err) {
                    console.log(err.track);
                });
            },
            handleBack: function () {
                this.$router.go(-1);
            }
        }
    };
</script>
