from django.conf.urls import url
from colab_app import views

urlpatterns = [
    url(r'^$', views.index, name='index'),
]
