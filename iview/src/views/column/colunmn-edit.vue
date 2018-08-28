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
                        <FormItem label="父分类">
                            <Select  v-model='type_id'>
                                <Option value="0">作为顶级</Option>
                                <Option v-for="(name, id) in type" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                        </FormItem>
                        <FormItem label="分类名称">
                            <Input v-model="course_type" icon="android-list"/>
                        </FormItem>
                        <FormItem v-model='status' class="margin-top-20" label="状态:">
                            <RadioGroup v-model="status">
                                <Radio label="1">启用</Radio>
                                <Radio label="0">禁用</Radio>
                            </RadioGroup>
                        </FormItem>
                        <FormItem class="margin-top-20" label="备注">
                            <textarea name="remark" v-model="remark" id="remark" cols="180" rows="5">
                            </textarea>
                        </FormItem>
                        <FormItem>
                            <Button type="primary" @click="SubmitType">Submit</Button>
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
        data: function () {
            return {
                type_id: '0',
                type: {},
                course_type: '',
                remark: '',
                status: '1'
            };
        },
        created: function () {
            this.getTypeInfo();
            this.getTypeList();
        },
        methods: {
            getTypeList: function () {
                let that = this;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                api: '/v1/course_type/getTableTree',
                                user_type: 3
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.type = that.array_map(response.data, 'id', 'course_type');
                    } else {
                        that.type = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            getTypeInfo: function () {
                var that = this;
                this.id = this.$route.params.id;
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/course_type/read',
                                id: this.id
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.course_type = response.data.course_type;
                        that.remark = response.data.remark;
                        that.status = response.data.status.toString();
                        that.type_id = response.data.parent_id.toString();
                        console.log(that.type_id);
                    } else {
                        that.$Message.error(response.msg);
                    }
                }).catch(function (err) {
                    console.log(11);
                    console.log(err.track);
                });
            },
            SubmitType: function () {
                var that = this;
                this.type_id = this.$route.params.type_id;
                if (this.course_type.length === 0) {
                    this.$Message.error('标题不能为空');
                    return false;
                }
                axios.get(
                    'http://mooc.com/v1/proxy/index',
                    {
                        params:
                            {
                                user_type: 3,
                                api: '/v1/course_type/update',
                                course_type: this.course_type,
                                parent_id: this.parent_id,
                                remark: this.remark,
                                status: this.status,
                                id: this.type_id
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    console.log(response);
                    if (response.status === 1) {
                        that.$Modal.info({
                            title: '修改',
                            content: '修改成功',
                            onOk: function () {
                                that.$router.push({name: 'course_type_index'});
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
            },
            handleUpload: function () {

            }
        }
    };
</script>
