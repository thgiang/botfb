<template>
    <div class="container">
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="far fa-heart"></i>
                            Cài đặt Bot tương tác Facebook
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form class="form-horizontal">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Cookie</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control"
                                               placeholder="Nhập Cookie" v-model="formData.cookie">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Tên gợi nhớ</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" v-model="formData.name"
                                               placeholder="Tên gợi nhớ">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Proxy để nuôi</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" v-model="formData.proxy"
                                               placeholder="Định dạng IP:Port">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Tương tác với</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" v-model="formData.bot_target">
                                            <option value="friend_and_fanpage">Chỉ bài viết từ bạn bè và Fanpage
                                            </option>
                                            <option value="group">Chỉ bài viết từ Group</option>
                                            <option value="all">Tất cả bài viết trên Newfeed</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Like dạo</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" v-model="formData.reaction_on">
                                            <option :value=true>Bật</option>
                                            <option :value=false>Tắt</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-show="formData.reaction_on">
                                    <div class="form-group row">
                                        <div class="col-md-6 p-0">
                                            <label class="col-sm-8 col-form-label">Mỗi lần like cách nhau</label>
                                            <div class="col-sm-12">
                                                <select class="form-control" v-model="formData.reaction_frequency">
                                                    <option v-for="minutes in 60" :value="minutes">{{ minutes }} phút
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 p-0">
                                            <label class="col-sm-6 col-form-label">Cảm xúc</label>
                                            <div class="col-sm-12">
                                                <select class="form-control" v-model="formData.reaction_type">
                                                    <option :value="0">Ngẫu nhiên</option>
                                                    <option :value="1">LIKE (Thích)</option>
                                                    <option :value="2">LOVE (Yêu thích)</option>
                                                    <option :value="16">CARE (Thương thương)</option>
                                                    <option :value="4">HAHA</option>
                                                    <option :value="3">WOW</option>
                                                    <option :value="6">SAD (Buồn)</option>
                                                    <option :value="8">ANGRY (Phẫn nộ)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Comment dạo</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" v-model="formData.comment_on">
                                            <option :value=true>Bật</option>
                                            <option :value=false>Tắt</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-show="formData.comment_on">
                                    <div class="form-group row">
                                        <div class="col-md-4 p-0">
                                            <label class="col-sm-8 col-form-label">Mỗi lần comment cách nhau</label>
                                            <div class="col-sm-12">
                                                <select class="form-control" v-model="formData.comment_frequency">
                                                    <option v-for="minutes in 60" :value="minutes">{{ minutes }} phút
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 p-0">
                                            <label class="col-sm-6 col-form-label">Comment kèm ảnh</label>
                                            <div class="col-sm-12">
                                                <select class="form-control" v-model="comment_use_image"
                                                        @change="comment_use_image===true?comment_use_sticker=!comment_use_image:null">
                                                    <option :value=true>Bật</option>
                                                    <option :value=false>Tắt</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 p-0">
                                            <label class="col-sm-8 col-form-label">Comment kèm sticker</label>
                                            <div class="col-sm-12">
                                                <select class="form-control" v-model="comment_use_sticker"
                                                        @change="comment_use_sticker===true?comment_use_image=!comment_use_sticker:null">
                                                    <option :value=true>Bật</option>
                                                    <option :value=false>Tắt</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="comment_use_image">
                                        <label class="col-sm-2 col-form-label">Ảnh để comment</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control"
                                                   placeholder="Nhập url hình ảnh muốn comment (ví dụ: https://google.com/anhdep.jpg )"
                                                   v-model="formData.comment_image_url">
                                            <small class="text-muted">Nhập: <b>{ngaunhien}</b> nếu muốn random
                                                ảnh.</small>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="comment_use_sticker">
                                        <label class="col-sm-2 col-form-label">Sticker để comment</label>
                                        <div class="col-sm-10">
                                            <div class="input-group input-group-sm">
                                                <select class="form-control"
                                                        v-model="formData.comment_sticker_collection">
                                                    <option v-for="sticker_collection in sticker_collections_id"
                                                            :value="sticker_collection.id">{{ sticker_collection.name
                                                        }}
                                                    </option>
                                                </select>
                                                <span class="input-group-append">
                    <button type="button" class="btn btn-info btn-flat" @click="seeSticker">Xem Sticker</button>
                  </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Nội dung bình luận</label>
                                        <div class="col-sm-10">
					<textarea class="form-control" v-model="formData.comment_content" rows="3"
                              placeholder="Mỗi nội dung một dòng, hệ thống sẽ tự lấy ngẫu nhiên để bình luận
Lệnh: {icon} = random emoij | {name} = tên facebook chủ post | {ngay} {thang} {nam} {gio} {phut} = ngày, tháng, năm, giờ, phút | {enter} = xuống dòng"></textarea>
                                            <small class="text-muted">Mỗi nội dung một dòng, hệ thống sẽ tự lấy ngẫu nhiên để bình luận
                                                Lệnh: {icon} = random emoij | {name} = tên facebook chủ post | {ngay} {thang} {nam} {gio} {phut} = ngày, tháng, năm, giờ, phút | {enter} = xuống dòng</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Backlist (không
                                        tương tác trên những UID này)</label>
                                    <div class="col-sm-10">
                                    <textarea class="form-control" rows="3" v-model="formData.black_list"
                                              placeholder="Mỗi UID một dòng"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Whitelist (ưu tiên tương tác trên những UID
                                        này)</label>
                                    <div class="col-sm-10">
                                    <textarea class="form-control" rows="3" v-model="formData.white_list"
                                              placeholder="Mỗi UID một dòng"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label>Bắt đầu từ</label>
                                        <select class="form-control" v-model="formData.start_time">
                                            <option v-for="hour in 24" :value="hour-1">{{ hour -1 }} giờ</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Đến</label>
                                        <select class="form-control" v-model="formData.end_time">
                                            <option v-for="hour in 24" :value="hour - 1">{{ hour - 1 }} giờ</option>
                                        </select>
                                    </div>
                                </div>
                                <!--                                <div class="form-group row">-->
                                <!--                                    <label class="col-sm-2 col-form-label">Số ngày thuê</label>-->
                                <!--                                    <div class="col-sm-10">-->
                                <!--                                        <input type="number" class="form-control" id="thoigianthue" value="10"-->
                                <!--                                               onchange="checktien()" placeholder="Nhập số ngày muốn thuê">-->
                                <!--                                    </div>-->
                                <!--                                </div>-->
                                <!--                                <div class="col-12 text-center bold">-->
                                <!--                                    <div class="card">-->
                                <!--                                        <div class="card-body">-->
                                <!--                                            <h5>Tổng : <span class="font-weight-bold"><span class="text-primary"-->
                                <!--                                                                                            id="starnhat">21,000</span></span>-->
                                <!--                                                <small>vnđ</small></h5>-->
                                <!--                                            <h6 class="mb-0">Giá Bot Reaction của bạn là <span-->
                                <!--                                                class="text-primary">2,100</span> <small>vnđ</small>/ngày</h6>-->
                                <!--                                        </div>-->
                                <!--                                    </div>-->
                                <!--                                </div>-->
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <button type="button" class="btn btn-block btn-success btn-sm"
                                                @click="addBot"><i
                                            class="fas fa-shopping-cart"></i>
                                            TẠO BOT
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </form>
                    </div>
                    <!-- /.card-body -->
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import Swal from 'sweetalert2'

    const axios = require('axios');
    export default {
        mounted() {
            this.formData.comment_sticker_collection = this.sticker_collections_id[0].id;
        },
        data() {
            return {
                comment_use_sticker: false,
                comment_use_image: false,
                formData: {
                    cookie: '',
                    name: '',
                    proxy: '',
                    bot_target: 'all',

                    reaction_on: false,
                    reaction_frequency: 5,
                    reaction_type: 0,

                    comment_on: false,
                    comment_frequency: 5,
                    comment_image_url: '',
                    comment_sticker_collection: '',
                    comment_content: '',

                    start_time: 8,
                    end_time: 20,

                    black_list: '',
                    white_list: '',
                },
                sticker_collections_id: [
                    {
                        'id': '2707173052857060', 'name': 'Stay Proud'
                    },
                    {
                        'id': '868343777006456', 'name': 'Kumo'
                    },
                    {
                        'id': '373003673617872', 'name': 'Ultra Usagyuuun'
                    },
                    {
                        'id': '227226091712383', 'name': 'Choco Bunny &amp; Coco'
                    },
                    {
                        'id': '1327347240788534', 'name': 'Hey buddy'
                    },
                    {
                        'id': '1028205884242215', 'name': 'In This Together'
                    },
                    {
                        'id': '528976084685064', 'name': 'Scandinavian Spring'
                    },
                    {
                        'id': '1064631583883264', 'name': 'Sugar Cubs in Love'
                    },
                    {
                        'id': '475157756481154', 'name': 'Love Me Tender'
                    },
                    {
                        'id': '2713710628707865', 'name': 'A Nose for Fun'
                    },
                    {
                        'id': '164593323744306', 'name': 'Lunar New Year'
                    },
                    {
                        'id': '572766476871965', 'name': 'Zizi and Pog'
                    },
                    {
                        'id': '1248025315393603', 'name': 'Lemon &amp; Sugar'
                    },
                    {
                        'id': '418290432140538', 'name': 'Kiss, Love, Pucca'
                    },
                    {
                        'id': '2259284510847655', 'name': 'It\'s A Dog\'s Life'
                    },
                    {
                        'id': '493341424732623', 'name': 'Tonton Friends Returns'
                    },
                    {
                        'id': '2353401344912532', 'name': 'Let\'s talk'
                    },
                    {
                        'id': '447332292534373', 'name': 'Quirks'
                    },
                    {
                        'id': '368356154101753', 'name': 'Mini Usagyuuun'
                    },
                    {
                        'id': '840869895965068', 'name': 'Amigos Alebrijes'
                    },
                    {
                        'id': '808449312598541', 'name': 'Rilakkuma'
                    },
                    {
                        'id': '843950799338121', 'name': 'F.U.N. with SpongeBob'
                    },
                    {
                        'id': '2519037555037811', 'name': 'Happy Mimi and Neko'
                    },
                    {
                        'id': '434436907341939', 'name': 'Annoying Rabbits'
                    },
                    {
                        'id': '2005763693052429', 'name': 'Stretch'
                    },
                    {
                        'id': '325514741681034', 'name': 'Rico\'s Sweet Life'
                    },
                    {
                        'id': '291104995177257', 'name': 'Fierce Out Loud'
                    },
                    {
                        'id': '911378149212412', 'name': 'BT21 Best Friends Forever'
                    },
                    {
                        'id': '467915713749082', 'name': 'Mabuhay'
                    },
                    {
                        'id': '641189223018104', 'name': 'Squirrel tails'
                    },
                    {
                        'id': '2366501506707920', 'name': 'Hyper Usagyuuun'
                    },
                    {
                        'id': '2041010162793124', 'name': 'Sweet QooBee'
                    },
                    {
                        'id': '1775273559380015', 'name': 'Chummy Chum Chums'
                    },
                    {
                        'id': '516558918806470', 'name': 'Moodies'
                    },
                    {
                        'id': '2552818778067386', 'name': 'Fiery Chicken Bro'
                    },
                    {
                        'id': '2309902989245861', 'name': 'Mentori'
                    },
                    {
                        'id': '419562695480565', 'name': 'Hugs and kisses'
                    },
                    {
                        'id': '603225566789710', 'name': 'Rosa\'s New Year'
                    },
                    {
                        'id': '375993709618173', 'name': 'Sunny'
                    },
                    {
                        'id': '178735036368023', 'name': 'Puppy love'
                    },
                    {
                        'id': '361256481293555', 'name': 'Mimi and Neko'
                    },
                    {
                        'id': '340001526583795', 'name': 'BT21 Love &amp; Peace'
                    },
                    {
                        'id': '198863457655971', 'name': 'Merry and bright'
                    },
                    {
                        'id': '294527934502495', 'name': 'Friends Everywhere'
                    },
                    {
                        'id': '1738133489645835', 'name': 'Tiny Snek and Friends'
                    },
                    {
                        'id': '237317020421291', 'name': 'BugCat Capoo'
                    },
                    {
                        'id': '514199122346369', 'name': 'Skeleton Crew'
                    },
                    {
                        'id': '1070394853111332', 'name': 'Perritos'
                    },
                    {
                        'id': '1758110050953186', 'name': 'Best of Breed'
                    },
                    {
                        'id': '1926229867416007', 'name': 'Speedy Usagyuuun'
                    },
                    {
                        'id': '274837110019942', 'name': 'Bob\'s Burgers'
                    },
                    {
                        'id': '2292474314307932', 'name': 'Leaflets'
                    },
                    {
                        'id': '440594196429579', 'name': 'Lovely Sugar Cubs'
                    },
                    {
                        'id': '546830775700754', 'name': 'More Little Mushroom'
                    },
                    {
                        'id': '568543226876672', 'name': 'Bold Betakkuma'
                    },
                    {
                        'id': '2141906962718688', 'name': 'Doodlings'
                    },
                    {
                        'id': '780909388782655', 'name': 'Noo-Hin Dance'
                    },
                    {
                        'id': '273007503470209', 'name': 'Modern Love'
                    },
                    {
                        'id': '657415894610578', 'name': 'BT21 Absolute Charm'
                    },
                    {
                        'id': '1426727610738683', 'name': 'Trés Chic'
                    },
                    {
                        'id': '1780132078895766', 'name': 'Fresh Fruit'
                    },
                    {
                        'id': '195758424483389', 'name': 'Everyday Business Fish'
                    },
                    {
                        'id': '176764613013402', 'name': 'Uncle Drew'
                    },
                    {
                        'id': '181539529357024', 'name': 'Arriba'
                    },
                    {
                        'id': '230459684205444', 'name': 'Love of Football'
                    },
                    {
                        'id': '399314183918821', 'name': 'Dough Banjuk'
                    },
                    {
                        'id': '180149872577874', 'name': 'Football Scarves 2018 (M-Z)'
                    },
                    {
                        'id': '451722341935748', 'name': 'Football Scarves 2018 (A-M)'
                    },
                    {
                        'id': '785487011650668', 'name': 'Bright Days'
                    },
                    {
                        'id': '878246095872025', 'name': 'Piyomaru and Friends'
                    },
                    {
                        'id': '974381839378744', 'name': 'Pinned'
                    },
                    {
                        'id': '1841025489283560', 'name': 'Tonton Friends'
                    },
                    {
                        'id': '196164350993249', 'name': 'Avengers: Infinity War'
                    },
                    {
                        'id': '162366894330481', 'name': 'Playing with Drogo'
                    },
                    {
                        'id': '1880330882182135', 'name': 'La Couleur'
                    },
                    {
                        'id': '1747078988936686', 'name': 'QooBee Agapi'
                    },
                    {
                        'id': '184002335550755', 'name': 'Usagyuuun'
                    },
                    {
                        'id': '154963288552012', 'name': 'Snoopy and Friends'
                    },
                    {
                        'id': '177571232788482', 'name': 'Streetwise'
                    },
                    {
                        'id': '2046733402321123', 'name': 'Dragon Boy'
                    },
                    {
                        'id': '1979159735648140', 'name': 'Box Girl'
                    },
                    {
                        'id': '655275024656259', 'name': 'Superzeroes'
                    },
                    {
                        'id': '164828414279110', 'name': 'Moody foodies'
                    },
                    {
                        'id': '785404898297531', 'name': 'Daily Duncan Vol. 1'
                    },
                    {
                        'id': '1610525125675194', 'name': 'Family Guy'
                    },
                    {
                        'id': '1849950268551094', 'name': 'As Per Usual'
                    },
                    {
                        'id': '1433214683459267', 'name': 'South Park'
                    },
                    {
                        'id': '1950458778556013', 'name': 'Teenage Mutant Ninja Turtles'
                    },
                    {
                        'id': '133244093990978', 'name': 'The Mask Singer'
                    },
                    {
                        'id': '1928685787364412', 'name': 'Piñata Poi'
                    },
                    {
                        'id': '318707375263756', 'name': 'Justice League'
                    },
                    {
                        'id': '143981766203673', 'name': 'BoJack Horseman'
                    },
                    {
                        'id': '918849354932447', 'name': 'Betakkuma 2.0'
                    },
                    {
                        'id': '114313875829887', 'name': 'Brown and Friends'
                    },
                    {
                        'id': '520795074939892', 'name': 'Stranger Things'
                    },
                    {
                        'id': '1904664526450375', 'name': 'Giggles and Ghouls'
                    },
                    {
                        'id': '1301708839958087', 'name': 'Everwing'
                    },
                    {
                        'id': '510423522639704', 'name': 'Tricksters'
                    },
                    {
                        'id': '1756431077991671', 'name': 'Happy Diwali'
                    },
                    {
                        'id': '1128765190602226', 'name': 'Makin\' Plans'
                    },
                    {
                        'id': '318343725303934', 'name': 'My Little Pony: The Movie'
                    },
                    {
                        'id': '118037345577148', 'name': 'Durga Puja Celebration'
                    },
                    {
                        'id': '1193273174104906', 'name': 'Noo-Hin'
                    },
                    {
                        'id': '269029946768318', 'name': 'Palabritas'
                    },
                    {
                        'id': '420705898287363', 'name': 'Bana &amp; Nana'
                    },
                    {
                        'id': '1458455310908332', 'name': 'The Defenders'
                    },
                    {
                        'id': '1356976894348433', 'name': 'La Frenchitude'
                    },
                    {
                        'id': '1910875999174847', 'name': 'Wrestlers Rematch'
                    },
                    {
                        'id': '320309015084066', 'name': 'Rimau'
                    },
                    {
                        'id': '570287033417531', 'name': 'Jeffrey'
                    },
                    {
                        'id': '248702998966195', 'name': 'Chicken Bro'
                    },
                    {
                        'id': '193082274544043', 'name': 'Tonton friends'
                    },
                    {
                        'id': '134639373761374', 'name': 'Rick and Morty'
                    },
                    {
                        'id': '642555552594474', 'name': 'Zanimaux'
                    },
                    {
                        'id': '433357603716844', 'name': 'The Emoji Movie'
                    },
                    {
                        'id': '467960563567882', 'name': 'Spider-Man: Homecoming'
                    },
                    {
                        'id': '265287087232325', 'name': 'Darling Sugar Cubs'
                    },
                    {
                        'id': '1616626381741531', 'name': 'Despicable Me 3'
                    },
                    {
                        'id': '827135594120136', 'name': 'Betakkuma'
                    },
                    {
                        'id': '277725192675558', 'name': 'Happy Rosa'
                    },
                    {
                        'id': '1810107755873702', 'name': 'Little Mushroom and Chubby Wolf'
                    },
                    {
                        'id': '840527599421135', 'name': 'Baahubali 2'
                    },
                    {
                        'id': '157616411398008', 'name': 'Simon\'s Cat'
                    },
                    {
                        'id': '1180646208713106', 'name': 'Lovely Greetings'
                    },
                    {
                        'id': '1297723130348180', 'name': 'Yasuke'
                    },
                    {
                        'id': '1589813814664773', 'name': 'Little Brother Yam'
                    },
                    {
                        'id': '980831735351043', 'name': 'Kaleidoscope'
                    },
                    {
                        'id': '1893331484284817', 'name': 'Nope'
                    },
                    {
                        'id': '1283312071733027', 'name': 'Dragon Clan'
                    },
                    {
                        'id': '901685413268534', 'name': 'Smurfs: The Lost Village'
                    },
                    {
                        'id': '377525602633204', 'name': 'Power Rangers Film'
                    },
                    {
                        'id': '156862148154586', 'name': 'Fearless and Fabulous'
                    },
                    {
                        'id': '1440530709312039', 'name': 'Kingdom of Tigers'
                    },
                    {
                        'id': '344366532626228', 'name': 'Playful Piyomaru'
                    },
                    {
                        'id': '1195842697144501', 'name': 'No Regrets'
                    },
                    {
                        'id': '1421085547904322', 'name': 'Flu Season'
                    },
                    {
                        'id': '1822205454720955', 'name': 'Be Mine'
                    },
                    {
                        'id': '1844803329128898', 'name': 'Trash Doves'
                    },
                    {
                        'id': '136144223530599', 'name': 'Emoticat'
                    },
                    {
                        'id': '1769367983386927', 'name': 'Burgerworld'
                    },
                    {
                        'id': '1828432350762013', 'name': 'Fantastic Sumo'
                    },
                    {
                        'id': '353701958341952', 'name': 'Royal Flush'
                    },
                    {
                        'id': '1269371929749714', 'name': 'Si Juki'
                    },
                    {
                        'id': '1831437497128024', 'name': 'Holidays Happen'
                    },
                    {
                        'id': '1578937112436083', 'name': 'Rosa'
                    },
                    {
                        'id': '578026049032906', 'name': 'Jokukuma'
                    },
                    {
                        'id': '836533459822375', 'name': 'Salapao &amp; Numnim'
                    },
                    {
                        'id': '493674027424012', 'name': 'Executive Business Fish'
                    },
                    {
                        'id': '950029025130645', 'name': 'Trolls'
                    },
                    {
                        'id': '105124253152499', 'name': 'Sloth Party'
                    },
                    {
                        'id': '1334534989893884', 'name': 'Edmund J. Wizard'
                    },
                    {
                        'id': '1406802526000170', 'name': 'Business is Good'
                    },
                    {
                        'id': '691345474343037', 'name': 'Take it easy'
                    },
                    {
                        'id': '1134978266517169', 'name': 'Bust a Groove'
                    },
                    {
                        'id': '1113231172030567', 'name': 'Chin &amp; Su'
                    },
                    {
                        'id': '466527456878135', 'name': 'Best friends'
                    },
                    {
                        'id': '811971838879467', 'name': 'Scribble Squad'
                    },
                    {
                        'id': '1431978070438033', 'name': 'Dizzy dog'
                    },
                    {
                        'id': '139842316433725', 'name': 'Wild About India'
                    },
                    {
                        'id': '1045328075551442', 'name': 'Olympia'
                    },
                    {
                        'id': '1165896083432995', 'name': 'Suicide Squad'
                    },
                    {
                        'id': '250073048673475', 'name': 'Little gang'
                    },
                    {
                        'id': '695560480546373', 'name': 'Zut alors!'
                    },
                    {
                        'id': '892873567483257', 'name': 'Ghostbusters'
                    },
                    {
                        'id': '1484289138550712', 'name': 'Keener Critters'
                    },
                    {
                        'id': '464740130392200', 'name': 'The Maladroits'
                    },
                    {
                        'id': '1609756839314842', 'name': 'Fatherly Love'
                    },
                    {
                        'id': '1021229357960051', 'name': 'MiM &amp; Yam'
                    },
                    {
                        'id': '757596424341453', 'name': 'De boa'
                    },
                    {
                        'id': '1682182211996802', 'name': 'Ace the tennis star'
                    },
                    {
                        'id': '951752788207111', 'name': 'SpongeBob &amp; Friends'
                    },
                    {
                        'id': '1074551879267678', 'name': 'Political party time'
                    },
                    {
                        'id': '893297930789912', 'name': 'Little sailors'
                    },
                    {
                        'id': '1060824507294765', 'name': 'Motherly love'
                    },
                    {
                        'id': '1567107430247365', 'name': 'Little sweets'
                    },
                    {
                        'id': '1035715433106590', 'name': 'Blues Breakdown'
                    },
                    {
                        'id': '566631536819544', 'name': 'Kung Fury'
                    },
                    {
                        'id': '1588896124706040', 'name': 'Absolutely cheeky'
                    },
                    {
                        'id': '806436032800235', 'name': 'Happy campers'
                    },
                    {
                        'id': '471187263081645', 'name': 'Gopher on the Green'
                    },
                    {
                        'id': '958510290908516', 'name': 'Virtual reality check'
                    },
                    {
                        'id': '896379557111269', 'name': 'Hair Bandits'
                    },
                    {
                        'id': '534663290019986', 'name': 'Cricket matchup'
                    },
                    {
                        'id': '1142697739082878', 'name': 'Batman V Superman'
                    },
                    {
                        'id': '883825964986093', 'name': 'Moody Ninja'
                    },
                    {
                        'id': '559099264188570', 'name': '1600 Pandas Tour'
                    },
                    {
                        'id': '1522801858013799', 'name': 'Significant otters'
                    },
                    {
                        'id': '445624062303452', 'name': 'Kung Fu Panda'
                    },
                    {
                        'id': '901276153276888', 'name': 'Tweet Tweet Parakeet'
                    },
                    {
                        'id': '748070108672546', 'name': 'Revved Up'
                    },
                    {
                        'id': '776939622432878', 'name': 'Dearest Deer'
                    },
                    {
                        'id': '445224959017096', 'name': 'A Charlie Brown Christmas'
                    },
                    {
                        'id': '516120955203272', 'name': 'Holiday Cheer'
                    },
                    {
                        'id': '722010354492041', 'name': 'Likes'
                    },
                    {
                        'id': '150600088477411', 'name': 'Wanderful Dog'
                    },
                    {
                        'id': '1593399364232319', 'name': 'Man Tears'
                    },
                    {
                        'id': '1475258236115706', 'name': 'Hal the eagle'
                    },
                    {
                        'id': '1389786324656182', 'name': 'The Peanuts Movie'
                    },
                    {
                        'id': '1647559148821353', 'name': 'Calaveritas'
                    },
                    {
                        'id': '930680800344176', 'name': 'Trio of Terror'
                    },
                    {
                        'id': '1489930268000970', 'name': 'Manchester United'
                    },
                    {
                        'id': '844068685642428', 'name': 'Cosmic Stranger'
                    },
                    {
                        'id': '126268397716550', 'name': 'Angry Birds'
                    },
                    {
                        'id': '1656448724600361', 'name': 'Piske &amp; Usagi'
                    },
                    {
                        'id': '639613219506791', 'name': 'Sweet Sugar Cubs'
                    },
                    {
                        'id': '1448422572153567', 'name': 'Play Baseball'
                    },
                    {
                        'id': '893563144048816', 'name': 'League of Legends'
                    },
                    {
                        'id': '1610332432553376', 'name': 'Pig E. Banks'
                    },
                    {
                        'id': '523658474454511', 'name': 'Shiba Inu'
                    },
                    {
                        'id': '657245280992564', 'name': 'LEGO Minifigures 2'
                    },
                    {
                        'id': '747761758674299', 'name': 'The GaMERCaT'
                    },
                    {
                        'id': '1457725781188801', 'name': 'Serious Business Fish'
                    },
                    {
                        'id': '990016507686232', 'name': 'Maju Lion'
                    },
                    {
                        'id': '1615178832066247', 'name': 'Shelly'
                    },
                    {
                        'id': '741786469196391', 'name': 'Delightful Dweores'
                    },
                    {
                        'id': '831176403597463', 'name': 'Corporate Jungle'
                    },
                    {
                        'id': '1604283453134761', 'name': 'Nyanchi'
                    },
                    {
                        'id': '478467358978106', 'name': 'Glamour Sharks'
                    },
                    {
                        'id': '1598049900458312', 'name': 'Minions'
                    },
                    {
                        'id': '776015525830151', 'name': 'Tons of Text'
                    },
                    {
                        'id': '743123329109915', 'name': 'Hello Brown'
                    },
                    {
                        'id': '1597605870495314', 'name': 'Freej'
                    },
                    {
                        'id': '412800385495934', 'name': '8-bits of Awesome'
                    },
                    {
                        'id': '1064720546889412', 'name': 'Love, Bigli Migli'
                    },
                    {
                        'id': '450414231741467', 'name': 'Downer Dinos'
                    },
                    {
                        'id': '379151258956190', 'name': 'Joys of Parenthood'
                    },
                    {
                        'id': '413892222056590', 'name': 'SpongeBob'
                    },
                    {
                        'id': '734207773307735', 'name': 'Oakley in Action'
                    },
                    {
                        'id': '1402232530073824', 'name': 'Ninja Bear'
                    },
                    {
                        'id': '1509538075963110', 'name': 'Ulysses S. Unicorn'
                    },
                    {
                        'id': '1520967238133373', 'name': 'More Cutie Pets'
                    },
                    {
                        'id': '1554174724811575', 'name': 'Unchi &amp; Rollie'
                    },
                    {
                        'id': '478376198960746', 'name': 'Yes We Code'
                    },
                    {
                        'id': '773558389385012', 'name': 'Boo and Buddy'
                    },
                    {
                        'id': '597727260347990', 'name': 'Dance Party'
                    },
                    {
                        'id': '1542248112677306', 'name': 'MiM on the Move'
                    },
                    {
                        'id': '1601167933448458', 'name': 'Meow Town'
                    },
                    {
                        'id': '1464910093763537', 'name': 'Naughty Foods'
                    },
                    {
                        'id': '534113093340309', 'name': 'Carnival'
                    },
                    {
                        'id': '1426512574306366', 'name': 'Biscuit in Love'
                    },
                    {
                        'id': '380362042125235', 'name': 'Bigli Migli'
                    },
                    {
                        'id': '1471771296438153', 'name': 'Friendship'
                    },
                    {
                        'id': '546513698755653', 'name': 'Cece'
                    },
                    {
                        'id': '163261440543545', 'name': 'The Dam Keeper'
                    },
                    {
                        'id': '648176385296599', 'name': 'Tanuki'
                    },
                    {
                        'id': '806551902750978', 'name': 'Sports Talk'
                    },
                    {
                        'id': '288280648049331', 'name': 'Shaun the Sheep'
                    },
                    {
                        'id': '830501653637723', 'name': 'Taz'
                    },
                    {
                        'id': '379623498831660', 'name': 'Rose'
                    },
                    {
                        'id': '630879683653426', 'name': 'Super Tiny'
                    },
                    {
                        'id': '1588423274719818', 'name': '1600 Pandas Tour 2'
                    },
                    {
                        'id': '278288232365323', 'name': 'HamCat'
                    },
                    {
                        'id': '641536849275363', 'name': 'Home for the Holidays'
                    },
                    {
                        'id': '364958996964139', 'name': 'Elf'
                    },
                    {
                        'id': '823232174362427', 'name': 'More Tuzki'
                    },
                    {
                        'id': '745196488886117', 'name': 'Business Fish'
                    },
                    {
                        'id': '379110802214676', 'name': 'Mockingjay'
                    },
                    {
                        'id': '1530352590538883', 'name': 'Yuttari Dragon'
                    },
                    {
                        'id': '1407088142851607', 'name': 'Say Thanks'
                    },
                    {
                        'id': '1560929467455956', 'name': 'Eagle &amp; Snake'
                    },
                    {
                        'id': '1561180534105131', 'name': 'Sugar Cubs'
                    },
                    {
                        'id': '388311457987565', 'name': 'Masked Wrestler Q'
                    },
                    {
                        'id': '469098233229266', 'name': 'Día de los Muertoons'
                    },
                    {
                        'id': '361326460697326', 'name': 'Yarukizero'
                    },
                    {
                        'id': '698154393603985', 'name': 'Piyomaru'
                    },
                    {
                        'id': '628030680646582', 'name': 'MiM Strikes Back'
                    },
                    {
                        'id': '456590551149708', 'name': 'KinoKoko'
                    },
                    {
                        'id': '1458437024416926', 'name': 'Text Talk'
                    },
                    {
                        'id': '792892464106692', 'name': 'Bee &amp; PuppyCat'
                    },
                    {
                        'id': '516503721826458', 'name': 'Mr Baldy &amp; Friends'
                    },
                    {
                        'id': '279176205619845', 'name': 'Party Fowls'
                    },
                    {
                        'id': '1498463050394825', 'name': 'NuaNia'
                    },
                    {
                        'id': '312951815532305', 'name': 'Sinister Oyster'
                    },
                    {
                        'id': '1450745381824807', 'name': 'Momo'
                    },
                    {
                        'id': '910324698982712', 'name': 'Hamilton'
                    },
                    {
                        'id': '1433443863610452', 'name': 'Gumball'
                    },
                    {
                        'id': '696849087017923', 'name': 'The Expendables 3'
                    },
                    {
                        'id': '678589498829816', 'name': 'Ruby'
                    },
                    {
                        'id': '1433995543540088', 'name': 'On the Move'
                    },
                    {
                        'id': '615507871865804', 'name': 'Tuzki'
                    },
                    {
                        'id': '1505030896377384', 'name': 'Regular Show'
                    },
                    {
                        'id': '226558650835899', 'name': 'Oakley'
                    },
                    {
                        'id': '652775241483731', 'name': 'Adventure Time'
                    },
                    {
                        'id': '294986347348231', 'name': 'The Ref'
                    },
                    {
                        'id': '1403151373296780', 'name': 'Chumbak\'s Back'
                    },
                    {
                        'id': '243185129138639', 'name': 'Football!'
                    },
                    {
                        'id': '638809142866604', 'name': 'Pride'
                    },
                    {
                        'id': '373478616117398', 'name': 'Dweores'
                    },
                    {
                        'id': '648231481855700', 'name': 'Cutie Pets'
                    },
                    {
                        'id': '580494075391362', 'name': 'Happy Birthday'
                    },
                    {
                        'id': '518960468196486', 'name': 'Sunny Eggy'
                    },
                    {
                        'id': '394507800693326', 'name': 'Mugsy in Love'
                    },
                    {
                        'id': '583052028455201', 'name': 'Love Is in the Air'
                    },
                    {
                        'id': '419189941536188', 'name': 'Ya-Ya'
                    },
                    {
                        'id': '604586402930304', 'name': 'Moohan'
                    },
                    {
                        'id': '399090170226548', 'name': 'Opi'
                    },
                    {
                        'id': '497126107040101', 'name': 'Biscuit'
                    },
                    {
                        'id': '175139712676531', 'name': 'Facebook Foxes'
                    },
                    {
                        'id': '644205678955467', 'name': 'Waddles Winter'
                    },
                    {
                        'id': '682343041800099', 'name': 'LEGO Minifigures'
                    },
                    {
                        'id': '401249466651973', 'name': 'Stella Supernova'
                    },
                    {
                        'id': '456205387826240', 'name': 'Mugsy'
                    },
                    {
                        'id': '322137057929371', 'name': 'Waddles Holiday'
                    },
                    {
                        'id': '651075328247947', 'name': 'Funnyeve Holidays'
                    },
                    {
                        'id': '443157242457587', 'name': 'Kukuxumusu'
                    },
                    {
                        'id': '349828668496527', 'name': 'Blue Cat'
                    },
                    {
                        'id': '554377321316789', 'name': 'Pusheen Eats'
                    },
                    {
                        'id': '114487328748554', 'name': 'Hacker Girl'
                    },
                    {
                        'id': '449925911787610', 'name': 'Candy Crush'
                    },
                    {
                        'id': '653151044718019', 'name': 'Snoopy\'s Harvest'
                    },
                    {
                        'id': '528218897264685', 'name': 'Waddles Halloween'
                    },
                    {
                        'id': '652635514769823', 'name': 'Free Birds'
                    },
                    {
                        'id': '633721996647110', 'name': 'Heromals'
                    },
                    {
                        'id': '654473057897548', 'name': 'Mostropi'
                    },
                    {
                        'id': '158412501021042', 'name': 'Baach'
                    },
                    {
                        'id': '548455165189615', 'name': 'Cut the Rope'
                    },
                    {
                        'id': '379426362183248', 'name': 'Pandadog &amp; Friends'
                    },
                    {
                        'id': '206136712877697', 'name': 'Mikey'
                    },
                    {
                        'id': '531027906967251', 'name': 'Fat Rabbit Farm'
                    },
                    {
                        'id': '1398214440396739', 'name': 'Mobile Girl, MiM'
                    },
                    {
                        'id': '608185149201896', 'name': 'Plum'
                    },
                    {
                        'id': '497837993632037', 'name': 'Koko'
                    },
                    {
                        'id': '226596734155609', 'name': 'Hacker Boy'
                    },
                    {
                        'id': '507125109360152', 'name': 'Bigs and Yeti'
                    },
                    {
                        'id': '623386314362769', 'name': 'Anooki'
                    },
                    {
                        'id': '274529629351692', 'name': 'Snoopy\'s Moods'
                    },
                    {
                        'id': '162332820618243', 'name': 'Pandi'
                    },
                    {
                        'id': '210412585774633', 'name': 'Despicable Me 2'
                    },
                    {
                        'id': '154461574718018', 'name': 'Wide Eyes'
                    },
                    {
                        'id': '194382497352420', 'name': 'Tigerbell'
                    },
                    {
                        'id': '588824221128361', 'name': 'Skullington'
                    },
                    {
                        'id': '350357561732812', 'name': 'Pusheen'
                    },
                    {
                        'id': '631486316879443', 'name': 'Prickly Pear'
                    },
                    {
                        'id': '168400679982977', 'name': 'Napoli'
                    },
                    {
                        'id': '126361870881943', 'name': 'Meep'
                    },
                    {
                        'id': '582402521770727', 'name': 'Mango'
                    },
                    {
                        'id': '641022829246662', 'name': 'Hatch'
                    },
                    {
                        'id': '201013370048597', 'name': 'Happy-Go-Lucky'
                    },
                    {
                        'id': '150915865096002', 'name': 'First Mate'
                    },
                    {
                        'id': '134873503361580', 'name': 'Finch'
                    },
                    {
                        'id': '392308740866438', 'name': 'Bun'
                    },
                    {
                        'id': '646526598708184', 'name': 'Beast'
                    },
                    {
                        'id': '654439774571103', 'name': 'Banana'
                    },
                    {
                        'id': '203431974233359', 'name': '[News Feed] More Together: Holi'
                    }],
            }
        }, methods: {
            seeSticker: function () {
                window.open('https://www.facebook.com/stickers/' + this.formData.comment_sticker_collection, "_blank");
            },
            addBot: function () {
                if (!this.comment_use_sticker) {
                    this.formData.comment_sticker_collection = '';
                }
                if (!this.comment_use_image) {
                    this.formData.comment_image_url = '';
                }
                axios.post('/api/bots/save', this.formData)
                    .then(function (response) {
                        if (response.data.status === "error") {
                            Swal.fire({
                                icon: response.data.status,
                                text: response.data.message
                            });
                        } else {
                            Swal.fire({
                                icon: response.data.status,
                                text: response.data.message
                            });
                        }
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        }
    }
</script>
