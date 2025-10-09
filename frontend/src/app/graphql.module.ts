import { NgModule } from "@angular/core";
import { APOLLO_OPTIONS } from "apollo-angular";
import {
  InMemoryCache,
  ApolloLink,
  ApolloClientOptions,
} from "@apollo/client/core";
import { HttpLink } from "apollo-angular/http";
import { environment } from "../environments/environment.development";

const uri = environment.graphQLEndpoint;

export function createApollo(httpLink: HttpLink): ApolloClientOptions<any> {
  return {
    link: ApolloLink.from([httpLink.create({ uri })]),
    cache: new InMemoryCache(),
  };
}

@NgModule({
  providers: [
    {
      provide: APOLLO_OPTIONS,
      useFactory: createApollo,
      deps: [HttpLink],
    },
  ],
})
export class GraphQLModule {}
