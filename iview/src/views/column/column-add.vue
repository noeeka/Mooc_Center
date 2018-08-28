<style lang="less">
    @import './column-add.less';
</style>

<template>
    <div>
        <Row>
            <Col span="18">
                <Card>
                    <Form :label-width="80">
                        <FormItem label="父栏目">
                            <Select  v-model='parent_id'>
                                <Option value="0">作为顶级</Option>
                                <Option v-for="(name, id) in columnList" :value="id" :key="id" >{{ name }}</Option>
                            </Select>
                        </FormItem>
                        <FormItem label="栏目名称">
                            <Input v-model="title" icon="android-list"/>
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

    export default {
        name: 'video-edit',
        data: function () {
            return {
                parent_id: '0',
                columnList: {},
                title: '',
                remark: '',
                status: '1'
            };
        },
        created: function () {
            this.getColumnList();
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
                        that.parent_id = that.$route.params.id.toString();
                    } else {
                        that.type = {};
                    }
                }).catch(function (err) {
                    console.log(err.stack);
                });
            },
            SubmitColumn: function () {
                var that = this;
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
                                api: '/v1/column/save',
                                title: this.title,
                                parent_id: this.parent_id,
                                remark: this.remark,
                                status: this.status
                            }
                    }
                ).then(function (response) {
                    response = response.data;
                    if (response.status === 1) {
                        that.$Modal.info({
                            title: '添加',
                            content: '添加成功',
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
