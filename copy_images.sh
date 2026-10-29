#!/bin/bash

# Copy and rename images for new food items
# This script duplicates existing images and renames them for new items

cd resources/images/food-items

# APPETIZERS - Copy existing images and rename for new items (16-31)
echo "Copying Appetizer images..."
cp spring-rolls.jpg aloo-tikki-app-16.jpg
cp samosa.jpg chaat-papdi-app-17.jpg
cp pakora.jpg dahi-bhalle-app-18.jpg
cp chicken-wings.jpg vegetable-pakora-app-19.jpg
cp mozzarella-sticks.jpg cheese-pakora-app-20.jpg
cp calamari.jpg mushroom-pakora-app-21.jpg
cp bruschetta.jpg corn-pakora-app-22.jpg
cp nachos.jpg spinach-pakora-app-23.jpg
cp hummus.jpg paneer-fries-app-24.jpg
cp garlic-bread.jpg vegetable-spring-roll-app-25.jpg
cp paneer-tikka.jpg prawn-pakora-app-26.jpg
cp vegetable-cutlet.jpg fish-pakora-app-27.jpg
cp onion-bhaji.jpg chicken-pakora-app-28.jpg
cp chikhalwali.jpg lamb-seekh-kebab-app-29.jpg
cp seekh-kebab.jpg tandoori-mushroom-app-30.jpg
cp achari-tikka.jpg tandoori-paneer-app-31.jpg

# ENTREES - Copy existing images and rename for new items (16-32)
echo "Copying Entree images..."
cp tandoori-chicken.jpg tandoori-fish-ent-16.jpg
cp chicken-tikka.jpg tandoori-prawns-ent-17.jpg
cp galauti-kebab.jpg tandoori-lamb-ent-18.jpg
cp malai-tikka.jpg chicken-tikka-masala-ent-19.jpg
cp hariyali-tikka.jpg paneer-tikka-masala-ent-20.jpg
cp lemon-coriander-chicken.jpg lamb-tikka-masala-ent-21.jpg
cp butter-chicken.jpg rogan-josh-ent-22.jpg
cp lamb-kebab.jpg chettinad-chicken-ent-23.jpg
cp fish-tikka.jpg hyderabadi-biryani-chicken-ent-24.jpg
cp prawn-tikka.jpg lucknowi-biryani-ent-25.jpg
cp mixed-grill.jpg nihari-ent-26.jpg
cp achari-tikka.jpg korma-ent-27.jpg
cp shami-kebab.jpg dopiaza-ent-28.jpg
cp galauti-kebab.jpg vindaloo-ent-29.jpg
cp malai-tikka.jpg jalfrezi-ent-30.jpg
cp hariyali-tikka.jpg saag-chicken-ent-31.jpg
cp lemon-coriander-chicken.jpg methi-chicken-ent-32.jpg

# CARBS - Copy existing images and rename for new items (16-32)
echo "Copying Carb images..."
cp naan.jpg garlic-naan-carb-16.jpg
cp roti.jpg butter-naan-carb-17.jpg
cp paratha.jpg cheese-naan-carb-18.jpg
cp biryani.jpg paneer-naan-carb-19.jpg
cp pulao.jpg peshwari-naan-carb-20.jpg
cp fried-rice.jpg tandoori-roti-carb-21.jpg
cp brown-rice.jpg methi-paratha-carb-22.jpg
cp jeera-rice.jpg aloo-paratha-carb-23.jpg
cp lemon-rice.jpg gobi-paratha-carb-24.jpg
cp kulcha.jpg paneer-paratha-carb-25.jpg
cp puri.jpg vegetable-biryani-carb-26.jpg
cp bhatura.jpg chicken-biryani-carb-27.jpg
cp dosa.jpg lamb-biryani-carb-28.jpg
cp idli.jpg prawn-biryani-carb-29.jpg
cp basmati-rice.jpg vegetable-pulao-carb-30.jpg
cp brown-rice.jpg mushroom-pulao-carb-31.jpg
cp fried-rice.jpg egg-fried-rice-carb-32.jpg

# DESSERTS - Copy existing images and rename for new items (16-32)
echo "Copying Dessert images..."
cp kheer.jpg carrot-halwa-des-16.jpg
cp gulab-jamun.jpg moong-dal-halwa-des-17.jpg
cp rasgulla.jpg sooji-halwa-des-18.jpg
cp jalebi.jpg peda-des-19.jpg
cp payasam.jpg kaju-katli-des-20.jpg
cp halwa.jpg mysore-pak-des-21.jpg
cp barfi.jpg coconut-barfi-des-22.jpg
cp laddu.jpg milk-barfi-des-23.jpg
cp kulfi.jpg chocolate-barfi-des-24.jpg
cp falooda.jpg besan-laddu-des-25.jpg
cp chocolate-cake.jpg motichur-laddu-des-26.jpg
cp cheesecake.jpg coconut-laddu-des-27.jpg
cp brownie.jpg pista-kulfi-des-28.jpg
cp tiramisu.jpg mango-kulfi-des-29.jpg
cp mousse.jpg strawberry-falooda-des-30.jpg
cp kheer.jpg rose-falooda-des-31.jpg
cp gulab-jamun.jpg mango-cheesecake-des-32.jpg

# BEVERAGES - Copy existing images and rename for new items (16-32)
echo "Copying Beverage images..."
cp mango-lassi.jpg strawberry-lassi-bev-16.jpg
cp diet-lassi.jpg banana-lassi-bev-17.jpg
cp masala-chai.jpg pistachio-lassi-bev-18.jpg
cp green-tea.jpg rose-lassi-bev-19.jpg
cp iced-tea.jpg cardamom-tea-bev-20.jpg
cp lemonade.jpg ginger-tea-bev-21.jpg
cp fruit-juice.jpg tulsi-tea-bev-22.jpg
cp smoothie.jpg black-tea-bev-23.jpg
cp milkshake.jpg herbal-tea-bev-24.jpg
cp soft-drink.jpg iced-green-tea-bev-25.jpg
cp coffee.jpg mint-lemonade-bev-26.jpg
cp espresso.jpg orange-juice-bev-27.jpg
cp cappuccino.jpg apple-juice-bev-28.jpg
cp latte.jpg pomegranate-juice-bev-29.jpg
cp iced-coffee.jpg mango-smoothie-bev-30.jpg
cp mango-lassi.jpg strawberry-smoothie-bev-31.jpg
cp diet-lassi.jpg chocolate-milkshake-bev-32.jpg

echo "✅ All images copied and renamed successfully!"
echo "Total new images created: 84"
